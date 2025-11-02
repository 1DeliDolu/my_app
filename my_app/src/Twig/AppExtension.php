<?php

namespace App\Twig;

use App\Service\CartService;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private CartService $cartService,
        private Packages $assetPackages,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('resolve_image_path', [$this, 'resolveImagePath']),
        ];
    }

    public function getGlobals(): array
    {
        return [
            'cartItemCount' => $this->cartService->count(),
        ];
    }

    public function resolveImagePath(?string $path): ?string
    {
        if (null === $path) {
            return null;
        }

        $trimmed = trim($path);
        if ('' === $trimmed) {
            return null;
        }

        if (preg_match('#^https?://#i', $trimmed)) {
            return $trimmed;
        }

        if (str_starts_with($trimmed, '//')) {
            return 'https:' . $trimmed;
        }

        if (preg_match('#^(?:data|blob):#i', $trimmed)) {
            return $trimmed;
        }

        $normalized = str_replace('\\', '/', $trimmed);

        $publicPosition = stripos($normalized, '/public/');
        if (false !== $publicPosition) {
            $normalized = substr($normalized, $publicPosition + 8);
        }

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        $normalized = preg_replace('#/{2,}#', '/', $normalized);
        $normalized = ltrim($normalized, '/');

        if ('' === $normalized) {
            return null;
        }

        return $this->assetPackages->getUrl($normalized);
    }
}
