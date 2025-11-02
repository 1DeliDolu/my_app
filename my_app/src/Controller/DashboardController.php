<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $paidStatuses = ['Paid', 'Shipped', 'Completed'];

        if ($this->isGranted('ROLE_ADMIN')) {
            $totalUsers = (int) $em->createQuery('SELECT COUNT(u.id) FROM '.User::class.' u')->getSingleScalarResult();
            $totalProducts = (int) $em->createQuery('SELECT COUNT(p.id) FROM '.Product::class.' p')->getSingleScalarResult();
            $totalOrders = (int) $em->createQuery('SELECT COUNT(o.id) FROM '.Order::class.' o')->getSingleScalarResult();
            $totalRevenue = (float) $em->createQuery(
                'SELECT COALESCE(SUM(o.total), 0) FROM '.Order::class.' o WHERE o.status IN (:paid)'
            )
                ->setParameter('paid', $paidStatuses)
                ->getSingleScalarResult();

            $dailySales = $this->buildDailySalesDataset(
                $em,
                (new \DateTimeImmutable('-30 days'))->setTime(0, 0),
                $paidStatuses
            );

            $topProducts = $em->createQuery(
                'SELECT p.name AS name, COALESCE(SUM(oi.subtotal), 0) AS revenue, SUM(oi.quantity) AS qty
                 FROM '.OrderItem::class.' oi
                 JOIN oi.product p
                 JOIN oi.orderRef o
                 WHERE o.status IN (:paid)
                 GROUP BY p.id
                 ORDER BY revenue DESC'
            )
                ->setParameter('paid', $paidStatuses)
                ->setMaxResults(5)
                ->getArrayResult();

            $topProducts = array_map(
                static fn(array $row) => [
                    'name' => $row['name'],
                    'revenue' => (float) $row['revenue'],
                    'qty' => (int) $row['qty'],
                ],
                $topProducts
            );

            $recentOrders = $em->getRepository(Order::class)->findBy([], ['createdAt' => 'DESC'], 10);

            return $this->render('dashboard/admin.html.twig', [
                'user' => $user,
                'stats' => [
                    'users' => $totalUsers,
                    'products' => $totalProducts,
                    'orders' => $totalOrders,
                    'revenue' => $totalRevenue,
                ],
                'salesLabels' => $dailySales['labels'],
                'salesData' => $dailySales['data'],
                'topProducts' => $topProducts,
                'recentOrders' => $recentOrders,
            ]);
        }

        if ($this->isGranted('ROLE_EMPLOYEE')) {
            $totalProducts = (int) $em->createQuery('SELECT COUNT(p.id) FROM '.Product::class.' p')->getSingleScalarResult();
            $pendingOrders = (int) $em->createQuery('SELECT COUNT(o.id) FROM '.Order::class.' o WHERE o.status = :status')
                ->setParameter('status', 'Pending')
                ->getSingleScalarResult();

            $recentOrders = $em->getRepository(Order::class)->findBy([], ['createdAt' => 'DESC'], 10);

            return $this->render('dashboard/employee.html.twig', [
                'user' => $user,
                'stats' => [
                    'products' => $totalProducts,
                    'pendingOrders' => $pendingOrders,
                ],
                'recentOrders' => $recentOrders,
            ]);
        }

        $orders = $em->getRepository(Order::class)->findBy(['user' => $user], ['createdAt' => 'DESC'], 10);

        return $this->render('dashboard/customer.html.twig', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    #[Route('/employee/orders-status', name: 'app_employee_orders_status', methods: ['GET'])]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function ordersStatus(EntityManagerInterface $em): JsonResponse
    {
        $pending = (int) $em->createQuery('SELECT COUNT(o.id) FROM '.Order::class.' o WHERE o.status = :status')
            ->setParameter('status', 'Pending')
            ->getSingleScalarResult();

        return $this->json(['pending' => $pending]);
    }

    /**
     * @param list<string> $statuses
     * @return array{labels: list<string>, data: list<float>}
     */
    private function buildDailySalesDataset(EntityManagerInterface $em, \DateTimeImmutable $from, array $statuses): array
    {
        $orders = $em->createQueryBuilder()
            ->select('o')
            ->from(Order::class, 'o')
            ->where('o.createdAt >= :from')
            ->andWhere('o.status IN (:statuses)')
            ->setParameter('from', $from)
            ->setParameter('statuses', $statuses)
            ->orderBy('o.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $totals = [];
        foreach ($orders as $order) {
            if (!$order instanceof Order) {
                continue;
            }

            $createdAt = $order->getCreatedAt();
            if (!$createdAt instanceof \DateTimeInterface) {
                continue;
            }

            $key = $createdAt->format('Y-m-d');
            $totals[$key] = ($totals[$key] ?? 0.0) + (float) $order->getTotal();
        }

        ksort($totals);

        $labels = [];
        $data = [];
        foreach ($totals as $date => $value) {
            $formatted = \DateTimeImmutable::createFromFormat('Y-m-d', $date)?->format('d.m') ?? $date;
            $labels[] = $formatted;
            $data[] = (float) $value;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
