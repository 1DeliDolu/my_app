<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Address;
use App\Service\CartService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
final class CheckoutController extends AbstractController
{
    #[Route('/', name: 'app_checkout')]
    public function index(CartService $cartService): Response
    {
        $user = $this->getUser();
        $cartItems = $cartService->getDetailedItems();

        if (!$cartItems) {
            $this->addFlash('info', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart_index');
        }

        return $this->render('checkout/index.html.twig', [
            'cartItems' => $cartItems,
            'total' => $cartService->getTotal(),
            'user' => $user,
        ]);
    }

    #[Route('/confirm', name: 'app_checkout_confirm', methods: ['POST'])]
    public function confirm(Request $request, EntityManagerInterface $em, CartService $cartService): Response
    {
        $user = $this->getUser();
        $cartItems = $cartService->getDetailedItems();

        if (!$cartItems) {
            $this->addFlash('warning', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart_index');
        }

        $addressId = $request->request->getInt('address');
        $address = $em->getRepository(Address::class)->find($addressId);

        if (!$address || $address->getUser() !== $user) {
            $this->addFlash('danger', 'Please select a valid address.');
            return $this->redirectToRoute('app_checkout');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setShippingAddress($address);
        $now = new \DateTimeImmutable();
        $order->setCreatedAt($now);
        $order->setUpdatedAt($now);
        $order->setStatus('Pending');
        $order->setPaidAt(null);
        $total = 0.0;

        foreach ($cartItems as $cartItem) {
            $item = new OrderItem();
            $quantity = (int) $cartItem['quantity'];
            $unitPrice = (float) $cartItem['unitPrice'];
            $subtotal = (float) $cartItem['subtotal'];

            $item->setProduct($cartItem['product']);
            $item->setQuantity($quantity);
            $item->setUnitPrice(sprintf('%.2f', $unitPrice));
            $item->setSubtotal(sprintf('%.2f', $subtotal));
            $order->addOrderItem($item);
            $total += $subtotal;
        }

        $order->setTotal(sprintf('%.2f', $total));
        $em->persist($order);

        $em->flush();
        $cartService->clear();

        $this->addFlash('success', 'Order placed successfully!');
        return $this->redirectToRoute('app_checkout_success', ['id' => $order->getId()]);
    }

    #[Route('/success/{id}', name: 'app_checkout_success')]
    public function success(Order $order): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('checkout/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/pay/{id}', name: 'app_checkout_pay', methods: ['POST'])]
    public function pay(Order $order, EntityManagerInterface $em, MailService $mailer): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($order->getStatus() === 'Paid') {
            $this->addFlash('info', 'This order has already been paid.');
            return $this->redirectToRoute('app_checkout_success', ['id' => $order->getId()]);
        }

        sleep(2);

        $now = new \DateTimeImmutable();
        $order->setStatus('Paid');
        $order->setUpdatedAt($now);
        $order->setPaidAt($now);
        $em->flush();

        $mailer->sendOrderConfirmation($order);

        $this->addFlash('success', 'Payment completed successfully! Confirmation email sent.');

        return $this->redirectToRoute('app_checkout_success', ['id' => $order->getId()]);
    }
}
