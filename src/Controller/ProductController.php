<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/admin/products", name="GestionProduct")
    */
    public function index(ProductRepository $repo): Response
    {
        return $this->render('admin/listProducts.html.twig', [
            'products' => $repo->findAll() 
        ]);
    }

    /**
     * @Route("/admin/add/product", name="addProduct")
     * @Route("/admin/edit/product/{id}", name="editProduct")
     */
    public function gestionTrainers(Product $product = null, Request $req)
    {
        $messageFlash = "Produit Modifiée avec succées";
        if(!$product){
            $messageFlash = "Produit Ajoutée avec succées";
            $product = new Product();
        }
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $file = $req->files->get('ImageProduit')['produit'];
            if($file){
                $fileName = uniqid() .'.'. explode('.', $file->getClientOriginalName())[1]; 
                $product->setImage($fileName);
                $file->move($this->getParameter('upload_directory'), $fileName);
            }
            
            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $messageFlash);
            return $this->redirectToRoute('GestionProduct');
        }

        return $this->render('admin/addEditProduit.html.twig', [
            'formProduct' => $form->createView(),
            'action' => (!$product->getId()?"Modifier les informations de produit ":"Ajouter une nouveaux produit"),
        ]);
    }
}
