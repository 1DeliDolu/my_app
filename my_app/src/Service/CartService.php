<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private const CART_KEY = 'cart_items';

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository
    ) {}

    public function add(int $productId, int $quantity = 1): void
    {
        if ($quantity <= 0) {
            return;
        }

        $session = $this->getSession();
        if (null === $session) {
            return;
        }

        $cart = $session->get(self::CART_KEY, []);
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        $session->set(self::CART_KEY, $cart);
    }

    public function getDetailedItems(): array
    {
        $session = $this->getSession();
        if (null === $session) {
            return [];
        }

        $cart = $session->get(self::CART_KEY, []);
        $items = [];

        foreach ($cart as $productId => $qty) {
            $product = $this->productRepository->find($productId);
            if (!$product) {
                continue;
            }

            $unitPrice = (float) $product->getPrice();

            $items[] = [
                'product' => $product,
                'quantity' => $qty,
                'unitPrice' => $unitPrice,
                'subtotal' => $unitPrice * $qty,
            ];
        }

        return $items;
    }

    public function set(int $productId, int $quantity): void
    {
        $session = $this->getSession();
        if (null === $session) {
            return;
        }

        $cart = $session->get(self::CART_KEY, []);

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }

        $session->set(self::CART_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $session = $this->getSession();
        if (null === $session) {
            return;
        }

        $cart = $session->get(self::CART_KEY, []);
        unset($cart[$productId]);
        $session->set(self::CART_KEY, $cart);
    }

    public function getItemCount(): int
    {
        return $this->count();
    }

    public function getTotal(): float
    {
        return array_sum(array_column($this->getDetailedItems(), 'subtotal'));
    }

    public function clear(): void
    {
        $session = $this->getSession();
        if (null === $session) {
            return;
        }

        $session->remove(self::CART_KEY);
    }

    public function count(): int
    {
        $session = $this->getSession();
        if (null === $session) {
            return 0;
        }

        return (int) array_sum($session->get(self::CART_KEY, []));
    }

    private function getSession(): ?SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
