<?php

namespace App\DataFixtures;

use App\Entity\Carrier;
use App\Entity\Setting;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // ✅ Déclaration explicite de la propriété
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Transporteur de test
        $carrier = new Carrier();
        $carrier->setName('Chronopost Test');
        $carrier->setDescription('Transporteur de test');
        $carrier->setPrice(1099);
        $carrier->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($carrier);

        // Paramètres du site
        $setting = new Setting();
        $setting->setWebsiteName('C.G');
        $setting->setDescription('Boutique de test');
        $setting->setEmail('test@test.com');
        $setting->setPhone('0600000000');
        $setting->setLogo('logo.png');
        $setting->setCurrency('EUR');
        $setting->setTaxeRate(20);
        $setting->setFacebookLink('');
        $setting->setYoutubeLink('');
        $setting->setInstagramLink('');
        $setting->setStreet('Impasse du Couchant');
        $setting->setCity('Mèze');
        $setting->setCodePostal('34140');
        $setting->setState('FR');
        $setting->setCopyRight('© 2026 C.G');
        $manager->persist($setting);

        // Utilisateur de test
        $user = new User();
        $user->setEmail('costincianu.gheorghina@gmail.com');
        $user->setFullName('Gheorghina Costincianu');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword(
            $this->hasher->hashPassword($user, '123456')
        );
        $user->setIsVerified(true);
        $manager->persist($user);

        $manager->flush();
    }
}