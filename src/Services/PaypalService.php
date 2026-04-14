<?php 


namespace App\Services;

use App\Repository\PaymentMethodRepository;
use Symfony\Component\HttpFoundation\RequestStack;


class PaypalService
{
    /**
     * Initialise le service avec les dépendances injectées.
     * Note : l'accès à la session dans le constructeur est déconseillé
     * car elle peut ne pas être encore accessible à ce stade.
     * On stocke donc uniquement la référence pour un accès ultérieur.
     *
     * @param RequestStack      $requestStack      Pile de requêtes Symfony (accès à la session)
     * @param PaymentMethodRepository $paymentMethodRepo Repository des méthodes de paiement
    */
    public function __construct(   
        private RequestStack $requestStack,
        private PaymentMethodRepository $paymentMethodRepo,

    ) {
       // J'ai acces a ces methode par tout ou j'ai besoin dans mon CartService
       $this->session = $requestStack->getSession();
    }



    public function getPublicKey()
    {
        $config = $this->paymentMethodRepo->findOneByName("Paypal");
        // dd($config);
        if (($_ENV['APP_ENV']) === 'dev')    {
            // Mode développment
            return $config->getTestPublicApiKey();
        } else {
            // Mode production 
             return $config->getProdPublicApiKey();
        }
        
    }


    public function getPrivateKey()
    {
        $config = $this->paymentMethodRepo->findOneByName("Paypal");

        if (($_ENV['APP_ENV']) === 'dev'){
            // Mode développment
            return $config->getTestPrivateApiKey();
        } else {
            // Mode production 
             return $config->getProdPrivateApiKey();
        }
        
    }



    public function getBaseUrl()
    {
        $config = $this->paymentMethodRepo->findOneByName("Paypal");

        if (($_ENV['APP_ENV']) === 'dev'){
            // Mode développment
            return $config->getTestBaseUrl();
        } else {
            // Mode production 
             return $config->getProdBaseUrl();
        }
        
    }
}