<?php

namespace App\Controller;

use DateTime;
use App\Entity\Vehicule;
use App\Form\VehiculeType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehiculeController extends AbstractController 
{

    /**
     * @Route("/admin/vehicules", name="admin_app_vehicules")
     * @Route("/vehicules", name="liste_vehicules")
     */
     function adminVehicules(ManagerRegistry $doctrine)
    {
        $vehicules = $doctrine->getRepository(Vehicule::class)->findAll();
        return $this->render("vehicule/admin/gestionVehicules.html.twig", [
            "vehicules" => $vehicules
         ]
        );
    }

     /**
     *@Route("admin/ajout-vehicule", name="admin_ajout_vehicule")
     */
    function formVehicule(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        // si l'utilisateur n'est pas connecté
        if ( !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            $this->addFlash('error', "veuillez vous connecter avant de pouvoir acceder à cette page !");
            return $this->redirectToRoute('app_login');

        }
        // si l'utilisateur est connecté mais n'est pas admin
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', "vous etes un intrus, que faites vous ici ! ! ! !");
            return $this->redirectToRoute('app_home');
        }
        $vehicule = new Vehicule();

        $form = $this->createForm(VehiculeType::class, $vehicule);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $vehicule->setDateEnregistrement(new DateTime("now"));

             // on recupere l'image depuis le formulaire
            $file = $form->get('imageForm')->getData();
            //dd($file);
            //dd($vehicule);
            // le slug permet de modifier une chaine de caractéres : mot clé => mot-cle
            $fileName = $slugger->slug( $vehicule->getTitre() ) . uniqid() . '.' . $file->guessExtension();

            try{
                // on deplace le fichier image recuperé depuis le formulaire dans le dossier parametré dans la partie Parameters du fichier config/service.yaml, avec pour nom $fileName
                $file->move($this->getParameter('photos_vehicules'),  $fileName);
            }catch(FileExeption $e)
            {
                // gérer les exeptions en cas d'erreur durant l'upload
            }

            $vehicule->setPhoto($fileName);


           $manager = $doctrine->getManager();

           $manager->persist($vehicule);

           $manager->flush(); 

           return $this->redirectToRoute("admin_app_vehicules");
        }

        return $this->render("vehicule/formulaire.html.twig", [
            'formVehicule'=> $form->createView()
        ]);
    }

    /**
     * @Route("/admin/update-vehicule/{id}" , name="admin_update_vehicule");
     */
     function update(ManagerRegistry $doctrine, $id, Request $request, SluggerInterface $slugger) : Response
    {
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);

        // dd($vehicule);
        $form = $this->createForm(VehiculeType::class, $vehicule);

        $form->handleRequest($request);

        // on stock l'image du vehicule à mettre à jour
        $image = $vehicule->getphoto();

        if ($form->isSubmitted() && $form->isValid()) 
        {
           $vehicule->setDateEnregistrement(new DateTime("now"));
                if($form->isSubmitted() && $form->isValid())
        {
            // si une image a bien été ajouté au formulaire
            if($form->get('imageForm')->getData() )
            {
                // on recupere l'image du formulaire
                $imageFile = $form->get('imageForm')->getData();
    
                //on crée un nouveau nom pour l'image
                $fileName = $slugger->slug($vehicule->getTitre()) . uniqid() . '.' . $imageFile->guessExtension();
    
                //on deplace l'image dans le dossier parametré dans service.yaml
                try{
                    $imageFile->move($this->getParameter('photos_vehicules'), $fileName);
                }catch(FileException $e){
                    // gestion des erreur upload
                }
                $vehicule->setPhoto($fileName);
                 
                $vehicule->setDateEnregistrement(new DateTime("now"));
                
            }
                $manager= $doctrine->getManager();
                $manager->persist($vehicule);
                $manager->flush();
                
                $this->addFlash('success', 'Le vehicule a bien été mis ! '); 

                return $this->redirectToRoute('admin_app_vehicules');
        }
        }

        return $this->render("vehicule/formulaire.html.twig", [
            'formVehicule'=> $form->createView()
        ]);

    }

    /**
     * @Route("/delete-vehicule/{id}", name="admin_delete_vehicule")
     */
      function delete($id, ManagerRegistry $doctrine)
      {//$id aura
    
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
        $manager = $doctrine->getManager();
        $manager->remove($vehicule);
        $manager->flush(); 
    }
    

    

    
}

