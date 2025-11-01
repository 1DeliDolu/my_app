<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category')]
final class CategoryController extends AbstractController
{
    #[Route('/{slug}', name: 'app_category_show')]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Category $category, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        // Get all products related to this category (requires Product->category relation)
        $products = $productRepository->findBy(['category' => $category]);

        // also pass all categories for the sidebar
        $categories = $categoryRepository->findAll();

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    #[Route('/{slug}/products', name: 'app_category_products')]
    public function products(#[MapEntity(mapping: ['slug' => 'slug'])] Category $category, ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy(['category' => $category]);

        return $this->render('category/_products.html.twig', [
            'products' => $products,
        ]);
    }
}
