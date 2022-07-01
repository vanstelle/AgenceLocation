<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Vehicule;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        for($i=1; $i <= 10; $i++) {
            $article = new Vehicule();
            $article->setTitre("nom titre $i")
                    ->setMarque("Marque $i")
                    ->setModel("Description $i")
                    ->setDescription("Description $i")
                    ->setPhoto("Description $i")
                    ->setPrixJournalier($i)
                    ->setDateEnregistrement(new DateTime("now"));
                    $manager->persist($article);
          }       
        $manager->flush();
    }
}
