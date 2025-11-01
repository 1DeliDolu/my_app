<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use App\Entity\Category;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        // --- Türkçe açıklama: 8 adet kategori oluşturuyoruz ---
        $categoriesData = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic gadgets and devices.'],
            ['name' => 'Fashion', 'slug' => 'fashion', 'description' => 'Clothing, shoes, and accessories.'],
            ['name' => 'Home & Kitchen', 'slug' => 'home-kitchen', 'description' => 'Furniture and kitchen appliances.'],
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Fiction, non-fiction, and educational books.'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sporting goods and outdoor equipment.'],
            ['name' => 'Beauty', 'slug' => 'beauty', 'description' => 'Cosmetics and skincare products.'],
            ['name' => 'Toys', 'slug' => 'toys', 'description' => 'Toys and games for all ages.'],
            ['name' => 'Automotive', 'slug' => 'automotive', 'description' => 'Car accessories and tools.'],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setSlug($data['slug']);
            $category->setDescription($data['description']);
            $manager->persist($category);
            $categories[] = $category;
        }

        // --- Türkçe açıklama: örnek ürünler oluşturuyoruz ve rastgele kategori atıyoruz ---
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName($faker->words(3, true));
            $product->setDescription($faker->paragraph());
            // price is stored as string in the entity, format accordingly
            $product->setPrice((string) number_format($faker->randomFloat(2, 10, 500), 2, '.', ''));
            $product->setStock((string) $faker->numberBetween(1, 100));
            $product->setImage('https://picsum.photos/seed/' . ($i + 1) . '/400/300');
            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setUpdatedAt(new \DateTimeImmutable());
            // assign a random category from the ones created above
            $product->setCategory($faker->randomElement($categories));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
