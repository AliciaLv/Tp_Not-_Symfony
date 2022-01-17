<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Command;
use App\Repository\CommandRepository;
use App\Entity\Product;
use App\Repository\ProductRepository;

class CommandController extends AbstractController
{
    /**
     * @Route("/command", name="command")
     */
    public function index(CommandRepository $commandRepository): Response
    {
        $commandRepository = $this->getDoctrine()->getRepository(Command::class);
        $commands = $commandRepository->findAll();

        return $this->render('command/index.html.twig', [
            'commands' => $commands,
        ]);
    }
    /**
     * @Route("/command/{id}", name="command.show")
     */
    public function show($id, ProductRepository $productRepository): Response
    {
        $commandRepository = $this->getDoctrine()->getRepository(Command::class);
        $command = $commandRepository->find($id);
        if (!$command)
        {
            throw $this->createNotFoundException('The product does not exist');
        }
        else
        {
            $products = $command->getProducts();
            $productRepository = $this->getDoctrine()->getRepository(Product::class);
            $total = 0;
            foreach($products as $prod)
            {
                $total = $total + $prod->getPrice();
            }

            return $this->render('command/show.html.twig', [
                'command' => $command,
                'products' => $products,
                'total' => $total,
            ]);
        }
    }
}
