<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Product;
use App\Entity\Command;
use App\Form\CommandType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(SessionInterface $session, ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        $cart = $session->get('panier', []);
        $products = [];
        $total = 0;
        if(!empty($cart))
        {
            foreach($cart as $id => $quantity)
            {
                $prod = $productRepository->find($id);
                $products[] = $prod;
                $total = $prod->getPrice() + $total;
            }
        }

        $command = new Command();
        $commandForm = $this->createForm(commandtype::class, $command);
        $commandForm->handleRequest($request);

        if ($commandForm->isSubmitted() && !empty($products))
        { 
            $command->setCreatedAt(new \DateTime);
            foreach($products as $prod)
            {
                $command->addProduct($prod);
            }
            $entityManager->persist($command);
            $entityManager->flush();
            $session->set('panier', null);
            $this->addFlash('success', "The order has been saved!");
            return $this->redirectToRoute('home');
        }
        elseif($commandForm->isSubmitted() && empty($products))
        {
            $this->addFlash('error', "The cart is empty !");
        }

        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'quantity' => $cart,
            'total' => $total,
            'commandForm' => $commandForm->createView(),
        ]);
    }
    /**
     * @Route("/cart/add/{id}", name="add")
     */
    public function add($id, SessionInterface $session)
    {
        if (!$id)
        {
            return $this->json('nok', 404);
        }
        else{
            $cart = $session->get('panier', []);
            // ...
            $cart[$id] = 1;
            // ...
            $session->set('panier', $cart);
            // ...
            return $this->json('ok', 200);
        }
    }
    /**
     * @Route("/cart/delete/{id}", name="delete")
     */
    public function delete($id, SessionInterface $session)
    {
        if (!$id)
        {
            return $this->json('nok', 404);
        }
        else{
            $cart = $session->get('panier', []);
            // ...
            unset($cart[$id]);
            // ...
            $session->set('panier', $cart);
            $this->addFlash('success', "L'article a bien été supprimé !");
            $this->json('ok', 200);
            return $this->redirectToRoute('cart');
        }
    }
}
