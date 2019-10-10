<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Upload;
use App\Form\ModifType;
use App\Form\UploadType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecretSantaController extends AbstractController
{
    /**
     * @Route("/instructions", name="instructions")
     */
    public function instructions()
    {
        return $this->render('secret_santa/instructions.html.twig', [
            'controller_name' => 'SecretSantaController',
        ]);
    }

    /**
     * @Route("/", name="rena_home")
     */
    public function home(){
        return $this->render('secret_santa/home.html.twig');
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile(){
        return $this->render('secret_santa/profile.html.twig');
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     */
    public function profileEdit(Request $request){

        $user = $this->getUser();
        $form = $this->createForm(ModifType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            
            return $this->redirectToRoute('profile');
        }

        return $this->render('secret_santa/profileEdit.html.twig', [
            'modifForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/cadeau", name="envoie")
     */
    public function envoie(Request $request, UserRepository $userRep){

        $upload = $this->getUser();
        $upload->setImage(false);

        $enfant = $userRep->getUserById($upload->getEnfant());

        $form = $this->createForm(UploadType::class, $upload);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $upload->getFichier();

            if ($file->guessExtension() == "jpg"){
                $upload->setImage(true);
            }

            $fileName = md5(uniqId()).'.'.$file->guessExtension();
            $file->move($this->getParameter('upload_directory'), $fileName);
            $upload->setFichier($fileName);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upload);
            $entityManager->flush();

            return $this->redirectToRoute('rena_home');
        }

        return $this->render('secret_santa/envoie.html.twig', [
            'uploadForm' => $form->createView(),
            'enfant_username'=> $enfant[0]["username"],
            'enfant_description'=>$enfant[0]["description"]
        ]);
    }


    /**
     * @Route("delete/", name="supprimer")
     */
    public function suppression(){
        $suppr = $this->getUser();

        $suppr->setFichier(NULL);
        $suppr->setImage(NULL);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($suppr);
        $entityManager->flush();

        return $this->redirectToRoute('envoie');
    }
}