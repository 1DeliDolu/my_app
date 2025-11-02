<?php

namespace App\Controller;

use App\Entity\Category;
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

        $categoryRows = $em->createQuery(
            'SELECT c.id AS id, c.name AS name
             FROM '.Category::class.' c
             ORDER BY c.name ASC'
        )->getArrayResult();

        $productRows = $em->createQuery(
            'SELECT p.id AS id, p.name AS name, c.id AS category_id
             FROM '.Product::class.' p
             LEFT JOIN p.category c
             ORDER BY c.name ASC, p.name ASC'
        )->getArrayResult();

        $categoriesForFilter = [];
        foreach ($categoryRows as $row) {
            $categoryId = (string) $row['id'];
            $categoriesForFilter[$categoryId] = [
                'id' => $categoryId,
                'name' => $row['name'],
                'products' => [],
            ];
        }

        $uncategorizedProducts = [];
        foreach ($productRows as $row) {
            $productData = [
                'id' => (string) $row['id'],
                'name' => $row['name'],
            ];

            if (null === $row['category_id']) {
                $uncategorizedProducts[] = $productData;
                continue;
            }

            $categoryKey = (string) $row['category_id'];
            if (!isset($categoriesForFilter[$categoryKey])) {
                $categoriesForFilter[$categoryKey] = [
                    'id' => $categoryKey,
                    'name' => 'Category #'.$categoryKey,
                    'products' => [],
                ];
            }

            $categoriesForFilter[$categoryKey]['products'][] = $productData;
        }

        $filterCategories = array_values($categoriesForFilter);

        if ($uncategorizedProducts !== []) {
            $filterCategories[] = [
                'id' => 'uncategorized',
                'name' => 'Uncategorized',
                'products' => $uncategorizedProducts,
            ];
        }

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
            'categoryFilters' => $filterCategories,
        ]);
    }

    #[Route('/sales-data', name: 'app_admin_sales_data', methods: ['GET'])]
    public function salesData(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $range = max(1, (int) $request->query->get('range', 30));
        $from = (new \DateTimeImmutable(sprintf('-%d days', $range)))->setTime(0, 0);

        $categoryParam = $request->query->get('category');
        $productParam = $request->query->get('product');

        $categoryId = null;
        $onlyUncategorized = false;

        if (\is_string($categoryParam) && $categoryParam !== '' && $categoryParam !== 'all') {
            if ('uncategorized' === $categoryParam) {
                $onlyUncategorized = true;
            } elseif (ctype_digit($categoryParam)) {
                $categoryId = (int) $categoryParam;
            }
        }

        $productId = null;
        if (\is_string($productParam) && $productParam !== '' && $productParam !== 'all' && ctype_digit($productParam)) {
            $productId = (int) $productParam;
        }

        $dailySales = $this->buildDailySalesDataset(
            $em,
            $from,
            self::PAID_STATUSES,
            $categoryId,
            $productId,
            $onlyUncategorized
        );

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
    private function buildDailySalesDataset(
        EntityManagerInterface $em,
        \DateTimeImmutable $from,
        array $statuses,
        ?int $categoryId = null,
        ?int $productId = null,
        bool $onlyUncategorized = false
    ): array
    {
        $orders = $em->createQueryBuilder()
            ->select('o', 'oi', 'p', 'c')
            ->from(Order::class, 'o')
            ->leftJoin('o.orderItems', 'oi')
            ->leftJoin('oi.product', 'p')
            ->leftJoin('p.category', 'c')
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
            if (null === $categoryId && null === $productId && false === $onlyUncategorized) {
                $totals[$key] = ($totals[$key] ?? 0.0) + (float) $order->getTotal();
                continue;
            }

            $matchingTotal = 0.0;
            foreach ($order->getOrderItems() as $orderItem) {
                if (!$orderItem instanceof OrderItem) {
                    continue;
                }

                $product = $orderItem->getProduct();

                if (null !== $productId) {
                    if (!$product || $product->getId() !== $productId) {
                        continue;
                    }
                } elseif ($onlyUncategorized) {
                    if ($product && null !== $product->getCategory()) {
                        continue;
                    }
                } elseif (null !== $categoryId) {
                    $category = $product?->getCategory();
                    if (!$category || $category->getId() !== $categoryId) {
                        continue;
                    }
                }

                $matchingTotal += (float) $orderItem->getSubtotal();
            }

            if ($matchingTotal > 0.0) {
                $totals[$key] = ($totals[$key] ?? 0.0) + $matchingTotal;
            }
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
