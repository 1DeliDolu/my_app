<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
final class AdminController extends AbstractController
{
    private const PAID_STATUSES = ['Paid', 'Shipped', 'Completed'];

    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $totalUsers = (int) $em->createQuery('SELECT COUNT(u.id) FROM '.User::class.' u')->getSingleScalarResult();
        $totalProducts = (int) $em->createQuery('SELECT COUNT(p.id) FROM '.Product::class.' p')->getSingleScalarResult();
        $totalOrders = (int) $em->createQuery('SELECT COUNT(o.id) FROM '.Order::class.' o')->getSingleScalarResult();
        $totalRevenue = (float) $em->createQuery(
            'SELECT COALESCE(SUM(o.total), 0) FROM '.Order::class.' o WHERE o.status IN (:paid)'
        )
            ->setParameter('paid', self::PAID_STATUSES)
            ->getSingleScalarResult();

        $dailySales = $this->buildDailySalesDataset(
            $em,
            (new \DateTimeImmutable('-30 days'))->setTime(0, 0),
            self::PAID_STATUSES
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
            ->setParameter('paid', self::PAID_STATUSES)
            ->setMaxResults(5)
            ->getArrayResult();

        $latestOrders = $em->getRepository(Order::class)->findBy([], ['createdAt' => 'DESC'], 10);

        return $this->render('admin/dashboard.html.twig', [
            'kpis' => [
                'users' => $totalUsers,
                'products' => $totalProducts,
                'orders' => $totalOrders,
                'revenue' => $totalRevenue,
            ],
            'salesLabels' => $dailySales['labels'],
            'salesData' => $dailySales['data'],
            'topProducts' => array_map(
                static fn(array $row) => [
                    'name' => $row['name'],
                    'revenue' => (float) $row['revenue'],
                    'qty' => (int) $row['qty'],
                ],
                $topProducts
            ),
            'latestOrders' => $latestOrders,
        ]);
    }

    #[Route('/sales-data', name: 'app_admin_sales_data', methods: ['GET'])]
    public function salesData(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $range = max(1, (int) $request->query->get('range', 30));
        $from = (new \DateTimeImmutable(sprintf('-%d days', $range)))->setTime(0, 0);

        $dailySales = $this->buildDailySalesDataset($em, $from, self::PAID_STATUSES);

        return $this->json([
            'labels' => $dailySales['labels'],
            'data' => $dailySales['data'],
        ]);
    }

    #[Route('/report/pdf', name: 'app_admin_report_pdf', methods: ['GET'])]
    public function generatePdfReport(EntityManagerInterface $em): Response
    {
        if (!class_exists(Dompdf::class)) {
            throw new \LogicException('PDF generation requires "dompdf/dompdf". Please run "composer require dompdf/dompdf".');
        }

        $totalUsers = (int) $em->createQuery('SELECT COUNT(u.id) FROM '.User::class.' u')->getSingleScalarResult();
        $totalProducts = (int) $em->createQuery('SELECT COUNT(p.id) FROM '.Product::class.' p')->getSingleScalarResult();
        $totalOrders = (int) $em->createQuery('SELECT COUNT(o.id) FROM '.Order::class.' o')->getSingleScalarResult();
        $totalRevenue = (float) $em->createQuery(
            'SELECT COALESCE(SUM(o.total), 0) FROM '.Order::class.' o WHERE o.status IN (:paid)'
        )
            ->setParameter('paid', self::PAID_STATUSES)
            ->getSingleScalarResult();

        $topCategories = $em->createQuery(
            'SELECT c.name AS name, COALESCE(SUM(oi.subtotal), 0) AS revenue
             FROM '.OrderItem::class.' oi
             JOIN oi.product p
             LEFT JOIN p.category c
             JOIN oi.orderRef o
             WHERE o.status IN (:paid)
             GROUP BY c.id, c.name
             ORDER BY revenue DESC'
        )
            ->setParameter('paid', self::PAID_STATUSES)
            ->setMaxResults(5)
            ->getArrayResult();

        $topCategories = array_map(
            static fn(array $row) => [
                'name' => $row['name'] ?? 'Uncategorized',
                'revenue' => (float) $row['revenue'],
            ],
            $topCategories
        );

        $latestOrders = $em->getRepository(Order::class)->findBy([], ['createdAt' => 'DESC'], 10);

        $html = $this->renderView('admin/pdf_report.html.twig', [
            'generatedAt' => new \DateTimeImmutable(),
            'totalUsers' => $totalUsers,
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'topCategories' => $topCategories,
            'latestOrders' => $latestOrders,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setIsRemoteEnabled(true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="dashboard_report.pdf"',
            ]
        );
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
