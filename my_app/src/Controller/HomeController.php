<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $products = $productRepository->findAll();
        $categories = $categoryRepository->findAll();

        $categoryCounts = [];
        foreach ($categories as $category) {
            $categoryCounts[$category->getSlug()] = $productRepository->count(['category' => $category]);
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
            'categories' => $categories,
            'categoryCounts' => $categoryCounts,
        ]);
    }

    #[Route('/products/all', name: 'app_all_products')]
    public function allProducts(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('category/_products.html.twig', [
            'products' => $products,
        ]);
    }
}
