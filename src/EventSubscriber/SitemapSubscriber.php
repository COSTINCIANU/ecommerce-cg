<?php

namespace App\EventSubscriber;

use App\Repository\ProductRepository;
use App\Repository\PageRepository;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ProductRepository $productRepository,
        private PageRepository $pageRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    public function populate(SitemapPopulateEvent $event): void
    {
        $this->addStaticUrls($event->getUrlContainer());
        $this->addProductUrls($event->getUrlContainer());
        $this->addPageUrls($event->getUrlContainer());
    }

    private function addStaticUrls(UrlContainerInterface $urls): void
    {
        $staticRoutes = [
            ['route' => 'app_home', 'priority' => 1.0, 'changefreq' => 'daily'],
            ['route' => 'app_login', 'priority' => 0.3, 'changefreq' => 'monthly'],
            ['route' => 'app_register', 'priority' => 0.3, 'changefreq' => 'monthly'],
            ['route' => 'app_contact_index', 'priority' => 0.5, 'changefreq' => 'monthly'],
        ];

        foreach ($staticRoutes as $route) {
            $urls->addUrl(
                new UrlConcrete(
                    $this->urlGenerator->generate($route['route'], [], UrlGeneratorInterface::ABSOLUTE_URL),
                    new \DateTimeImmutable(),
                    $route['changefreq'],
                    $route['priority']
                ),
                'default'
            );
        }
    }

    private function addProductUrls(UrlContainerInterface $urls): void
    {
        $products = $this->productRepository->findAll();

        foreach ($products as $product) {
            $urls->addUrl(
                new UrlConcrete(
                    $this->urlGenerator->generate('app_product_by_slug', ['slug' => $product->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                    new \DateTimeImmutable(),
                    'weekly',
                    0.8
                ),
                'products'
            );
        }
    }

    private function addPageUrls(UrlContainerInterface $urls): void
    {
        $pages = $this->pageRepository->findAll();

        foreach ($pages as $page) {
            $urls->addUrl(
                new UrlConcrete(
                    $this->urlGenerator->generate('app_page', ['slug' => $page->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                    new \DateTimeImmutable(),
                    'monthly',
                    0.5
                ),
                'pages'
            );
        }
    }
}