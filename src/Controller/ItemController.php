<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ItemController extends AbstractController
{
    #[Route('/item', name: 'item.index', methods: ['GET'])]
    public function index(ItemRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $items = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('pages/item/index.html.twig', [
            'items' => $items
        ]);
    }

    #[Route('/item/nouveau', name: 'item.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            $newImageName = uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move($this->getParameter('images_directory'), $newImageName);

            $item->setImage($newImageName);

            $manager->persist($item);
            $manager->flush();

            $this->addFlash('success', 'L\'item a bien été ajouté !');

            return $this->redirectToRoute('item.index');
        }

        return $this->render('pages/item/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
