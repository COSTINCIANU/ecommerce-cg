<?php

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
// use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

final class PageController extends AbstractController
{
    #[Route('/page/{slug}', name: 'app_page')]
    public function index(string $slug, PageRepository $pageRepo): Response
    {

    $page = $pageRepo->findOneBy(["slug" =>$slug]);
        if(!$page){
            // Redicrect to erreur page 404
            return $this->render('page/notfound.html.twig', [
                'controller_name' => 'PageController',
                'page' => $page,
            ]);
        }

        return $this->render('page/index.html.twig', [
            'controller_name' => 'PageController',
            'page' => $page,
        ]);
    }

    // public function index(string $slug, PageRepository $pageRepo,
    //     HtmlSanitizerInterface $sanitizer
    // ): Response 
    // {
    //     $page = $pageRepo->findOneBy(["slug" => $slug]);

    //     if (!$page) {
    //         return $this->render('page/notfound.html.twig', [
    //             'controller_name' => 'PageController',
    //             'page' => $page,
    //         ]);
    //     }

    //     $content = $sanitizer->sanitize($page->getContent());

    //     return $this->render('page/index.html.twig', [
    //         'controller_name' => 'PageController',
    //         'page' => $page,
    //         'content' => $content,
    //     ]);
    // }

    // #[Route('/page/notfound', name: 'app_page')]
    // public function notfound(): Response
    // {
    //     return $this->render('page/notfound.html.twig', [
    //         'controller_name' => 'PageController',
    //     ]);
    // }
}
