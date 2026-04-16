<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Repository\CategoryRepository;
use App\Repository\CollectionRepository;
use App\Repository\CommentRepository;
use App\Repository\OrderRepository;
use App\Repository\PageRepository;
use App\Repository\ProductRepository;
use App\Repository\SettingRepository;
use App\Repository\SlidersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private ProductRepository $productRepo;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }


    /**
     * Affiche la page d'accueil du site.
     *
     * Récupère et injecte dans la session toutes les données globales nécessaires
     * à l'affichage du layout (paramètres du site, menus, catégories, collections),
     * puis passe à la vue les sliders et les produits filtrés par type
     * (meilleures ventes, nouveautés, produits vedettes, offres spéciales).
     *
     * @param SettingRepository    $settingRepo    Repository des paramètres du site
     * @param SlidersRepository    $slidersRepo    Repository des sliders
     * @param CategoryRepository   $categoryRepos  Repository des catégories
     * @param CollectionRepository $collectionRepo Repository des collections
     * @param PageRepository       $pageRepo       Repository des pages (header/footer)
     * @param Request              $request        Requête HTTP courante
     * @return Response            La vue de la page d'accueil
     */
    #[Route('/', name: 'app_home')]
    public function index(
        SettingRepository $settingRepo,
        SlidersRepository $slidersRepo, 
        CategoryRepository $categoryRepos,
        CollectionRepository $collectionRepo,
        PageRepository $pageRepo,
        Request $request): Response
        {

        // Je récupères la session de l’utilisateur.
        $session = $request->getSession();  // récupérer la session utilisateur
      
        
        // Je  demandes à Doctrine de récupérer toutes les données de la table Settings.
        $data =  $settingRepo->findAll(); // récupérer tous les paramètres du site
        
        // Je récupères tous les sliders dans la base de données
        $sliders = $slidersRepo->findAll();  // récupérer tous les sliders
        
        // Je récupères tous les collections dans la base de données
        $collections = $collectionRepo->findBy(['isMega' => false]);  // récupérer tous les collections

        // Je affliche tous dans megaCollections dans la base de données
        $megaCollections = $collectionRepo->findBy(['isMega' => true]);  // récupérer tous les megaCollections
        
        // Je récupères un tableau de données
        $categories = $categoryRepos->findBy(['isMega' => true]); // récupérer tous les isMega dans le grand menu
        
        
       
       
        // Je stocke les données dans la session
        $session->set("setting", $data[0]);

        $headerPages = $pageRepo->findBy(['isHead' => true]);
        $footerPages = $pageRepo->findBy(['isFoot' => true]);


        // Je stocke les pages dans la session isHead et isFoot, isMega le gros menu haut
        $session->set("headerPages",  $headerPages); // isHead le haut
        $session->set("footerPages",  $footerPages); // isFoot le footer
        $session->set("categories", $categories);    // Collections afficher sur la page
        $session->set("megaCollections", $megaCollections);  // isMega le gros menu haut

        
            return $this->render('home/index.html.twig', [
                'controller_name' => 'HomeController',
                'sliders' =>  $sliders,
                'collections' => $collections,
                'productsBestSeller' => $this->productRepo->findBy(['isBestSeller' => true]),
                'productsNewArrival' => $this->productRepo->findBy(['isNewArrival' => true]),
                'productsFeatured'  => $this->productRepo->findBy(['isFeatured' => true]),
                'productsSpecialOffer' => $this->productRepo->findBy(['isSpecialOffer' => true]),
            ]);
    }



    #[Route('/product/{slug}', name: 'app_product_by_slug')]
    public function showProduct(
        string $slug,
        Request $request,
        CommentRepository $commentRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $em
    ): Response {
        // $product = $this->productRepo->findOneBy(['slug' => $slug]);
        $product = $this->productRepo->findBySlugWithRelated($slug);

        if (!$product) {
            return $this->redirectToRoute('app_error');
        }

        // Récupération des avis publiés du produit
        $comments = $commentRepository->findPublishedByProduct($product);
        $averageRating = $commentRepository->getAverageRating($product);

        // Initialisation des variables du formulaire
        $commentForm = null;
        $canComment = false;
        $alreadyCommented = false;

        // On vérifie si l'utilisateur est connecté
        if ($this->getUser()) {
            /** @var User $user */
            $user = $this->getUser();

            // Vérification : a-t-il une commande payée ?
            $canComment = $orderRepository->hasUserPaidOrder($user);

            // Vérification : a-t-il déjà commenté ce produit ?
            $alreadyCommented = $commentRepository->hasUserAlreadyCommented($user, $product);

            // On affiche le formulaire seulement si achat du produit et seulement a ce moment  l'user peut commenter si il a une commande passe déjà.
            if ($canComment && !$alreadyCommented) {
                $comment = new Comment();
                $commentForm = $this->createForm(CommentFormType::class, $comment);
                $commentForm->handleRequest($request);

                if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                    $comment->setAuthor($user);
                    $comment->setProduct($product);
                    $comment->setEmail($user->getEmail());
                    $comment->setIsPublished(false);
                    $comment->setCreatedAt(new \DateTimeImmutable());

                    $em->persist($comment);
                    $em->flush();

                    $this->addFlash(
                        'success',
                        'Merci pour votre avis ! Il sera publié après validation.'
                    );

                    return $this->redirectToRoute('app_product_by_slug', [
                        'slug' => $product->getSlug()
                    ]);
                }
            }
        }

        return $this->render('product/show_product_by_slug.html.twig', [
            'product'        => $product,
            'comments'       => $comments,
            'averageRating'  => $averageRating,
            'commentForm'    => $commentForm?->createView(),
            'canComment'     => $canComment,
            'alreadyCommented' => $alreadyCommented,
        ]);
    }



    /**
     * @param string $id
     * @return JsonResponse
     */
    #[Route('/product/get/{id}', name: 'app_product_by_id')]
    public function getProductById(string $id): JsonResponse
    {
        $product =  $this->productRepo->findOneBy(['id'=>$id]);
        if(!$product) {
            //erreur
            return $this->json(false);
        }

        return $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'imageUrls' => $product->getImageUrls(),
            'soldePrice' => $product->getSoldePrice(),
            'regularPrice' => $product->getRegularPrice(),
        ]);
    }



    
    #[Route('/error', name: 'app_error')]
    public function errorPage()
    {
        
        return $this->render('page/notfound.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
}
