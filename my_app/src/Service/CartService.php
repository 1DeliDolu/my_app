<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_KEY = 'cart_items';

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository
    ) {}

    public function add(int $productId, int $quantity = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_KEY, []);
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        $session->set(self::CART_KEY, $cart);
    }

    public function getDetailedItems(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_KEY, []);
        $items = [];

        foreach ($cart as $productId => $qty) {
            $product = $this->productRepository->find($productId);
            if ($product) {
                $items[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unitPrice' => $product->getPrice(),
                    'subtotal' => $product->getPrice() * $qty,
                ];
            }
        }

        return $items;
    }

    public function getTotal(): float
    {
        return array_sum(array_column($this->getDetailedItems(), 'subtotal'));
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->remove(self::CART_KEY);
    }
}
