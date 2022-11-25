<?php

namespace App\Controller;

use Exception;
use App\Entity\Trainer;
use App\Form\TrainerType;
use App\Repository\TrainerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrainerController extends AbstractController
{
    /**
     * @Route("/trainers", name="trainers")
     */
    public function trainers(TrainerRepository $repo): Response
    {
        return $this->render('trainer/listTrainers.html.twig', [
            'trainers' => $repo->findAll()
        ]);
    }

    /**
     * @Route("/admin/trainers", name="GestionTrainers")
     */
    public function index(TrainerRepository $repo): Response
    {
        return $this->render('admin/gestionTrainers.html.twig', [
            'trainers' => $repo->findAll()
        ]);
    }

        /**
     * @Route("/admin/add/trainer", name="addTrainer")
     * @Route("/admin/edit/trainer/{id}", name="editTrainer")
     */
    public function gestionTrainers(Trainer $trainer = null, Request $req)
    {
        $messageFlash = "Entreneur Modifiée avec succées";
        if(!$trainer){
            $messageFlash = "Entreneur Ajoutée avec succées";
            $trainer = new Trainer();
        }
        $form = $this->createForm(TrainerType::class, $trainer);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $file = $req->files->get('ImageTrainer')['trainer'];
            if($file){
                $fileName = uniqid() .'.'. explode('.', $file->getClientOriginalName())[1]; 
                $trainer->setImage($fileName);
                $file->move($this->getParameter('upload_directory'), $fileName);
            }
            
            $this->getDoctrine()->getManager()->persist($trainer);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $messageFlash);
            return $this->redirectToRoute('GestionTrainers');
        }

        return $this->render('admin/addEditTrainer.html.twig', [
            'formTrainer' => $form->createView(),
            'action' => ($trainer?"Ajouter Entraineur":"Ajouter une nouveaux entreneur"),
        ]);
    }

    /**
     * @Route("/admin/delete/trainer/{id}", name="suppTrainer")
     */
    public function deleteTrainer(Trainer $trainer)
    {
        try{
            $this->getDoctrine()->getManager()->remove($trainer);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Entreneura été supprimée avec succès');
        }catch(Exception $e){
            $this->addFlash('danger', 'ce Entreneur a plusieurs donnée dans notre base de donnée il faut le supprimée avant le supprimée');
        }
        
        
        return $this->redirectToRoute("GestionTrainers");
    }
}
