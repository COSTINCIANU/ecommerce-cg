<?php

namespace App\Controller\Admin;



use App\Controller\Admin\AddressCrudController;
use App\Controller\Admin\CarrierCrudController;
use App\Controller\Admin\CategoryCrudController;
use App\Controller\Admin\CollectionCrudController;
use App\Controller\Admin\CommentCrudController;
use App\Controller\Admin\ContactCrudController;
use App\Controller\Admin\OrderCrudController;
use App\Controller\Admin\PageCrudController;
use App\Controller\Admin\PaymentMethodCrudController;
use App\Controller\Admin\ProductCrudController;
use App\Controller\Admin\SettingCrudController;
use App\Controller\Admin\SlidersCrudController;
use App\Controller\Admin\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
// use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        // // ✅ Après
        // $url = $this->generateUrl('admin', [
        //     'crudController' => 'App\Controller\Admin\ContactCrudController',
        //     'crudAction'     => 'index',
        // ]);

        // return $this->redirect($url);
        return $this->render('admin/dashboard.html.twig');

        // $url = $this->container->get(AdminUrlGenerator::class)
        //     ->setController(ContactCrudController::class)
        //     ->setAction('index')
        //     ->generateUrl();

        // return $this->redirect($url);
    }
    // public function index(): Response 
    // {   
    //     return $this->redirectToRoute('admin_product_index');
    
    //     // https://symfony.com/bundles/EasyAdminBundle/current/dashboards.html#customizing-the-dashboard-contents
    //     // https://symfony.com/bundles/ux-chartjs/current/index.html;


    //     // Parent index reture la page de esayAdmin par default 
    //     // return parent::index();

    //     // Option 1. You can make your dashboard redirect to some common page of your backend

        
    //     // Option 2. You can make your dashboard redirect to different pages depending on the user
    //     //
    //     // if ('jane' === $this->getUser()->getUsername()) {
    //     //     return $this->redirectToRoute('...');
    //     // }

    //     // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
    //     // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
    //     // return $this->render('some/path/my-dashboard.html.twig');
    // } 
    

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Ecommerce Cg');
    }


    public function configureMenuItems(): iterable
    {
   
        return [

            yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            
            yield MenuItem::linkTo(ProductCrudController::class, 'Products', 'fas fa-list'),
            yield MenuItem::linkTo(CategoryCrudController::class, 'Categories', 'fas fa-tag'),
            yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fas fa-users'),
            yield MenuItem::linkTo(ContactCrudController::class, 'Messages', 'fas fa-message'),
            yield MenuItem::linkTo(CommentCrudController::class, 'Commentaires', 'fas fa-comments'),
            yield MenuItem::linkTo(AddressCrudController::class, 'Addresses', 'fa fa-address-card'),
            yield MenuItem::linkTo(PageCrudController::class, 'Pages', 'fas fa-file'),
            yield MenuItem::linkTo(CollectionCrudController::class, 'Collections', 'fas fa-panorama'),
            yield MenuItem::linkTo(SlidersCrudController::class, 'Sliders', 'fas fa-image'),
            yield MenuItem::linkTo(OrderCrudController::class, 'Orders', 'fas fa-shopping-cart'),
            yield MenuItem::linkTo(CarrierCrudController::class, 'Carriers', 'fas fa-car'),
            yield MenuItem::linkTo(PaymentMethodCrudController::class, 'Payment Methods', 'fas fa-landmark'),
            yield MenuItem::linkTo(SettingCrudController::class, 'Settings', 'fas fa-gear')

        ];
    }



}
