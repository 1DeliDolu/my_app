<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/orders')]
#[IsGranted('ROLE_USER')]
final class OrderController extends AbstractController
{
    #[Route('/', name: 'app_order_index')]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $em->getRepository(Order::class)->findBy(
                ['user' => $this->getUser()],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    #[Route('/{id}', name: 'app_order_show')]
    public function show(Order $order): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }
}
