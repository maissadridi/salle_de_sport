<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(UserRepository $repo): Response
    {
        return $this->render('admin/clients.html.twig', [
            'users' => $repo->findAll()
        ]);
    }
    /**
     * @Route("/admin/add/user", name="addUser")
     * @Route("/admin/edit/user/{id}", name="editUser")
     */
    public function inscription(User $user = null, Request $req, UserPasswordEncoderInterface $encoder, AuthorizationCheckerInterface $authChecker )
    {
        $messageFlash = "Adhérent Modifiée avec succées";
        if(!$user){
            $messageFlash = "Adhérent Ajoutée avec succées";
            $user = new User();
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $user->setCreatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $messageFlash);
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/addUser.html.twig', [
            'formUser' => $form->createView(),
            'action' => ($user?"Ajouter nouveaux adhérent":"Ajouter une nouveaux utilisateur"),
        ]);
    }

    /**
     * @Route("/admin/delete/user/{id}", name="suppUser")
     */
    public function deleteUser(User $user)
    {
        try{
            $this->getDoctrine()->getManager()->remove($user);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'client supprimée avec succès');
        }catch(Exception $e){
            $this->addFlash('danger', 'ce Client a plusieurs donnée dans notre base de donnée il faut le supprimée avant le supprimée');
        }
        
        
        return $this->redirectToRoute("admin");
    }
}
