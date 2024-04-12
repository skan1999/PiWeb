<?php

namespace App\Controller;

use App\Entity\LocationTerrain;
use App\Entity\Partie;
use App\Entity\User;
use App\Form\PartieFormType;
use App\Repository\PartieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/partie')]
class PartieController extends AbstractController
{
    #[Route('/', name: 'app_index_parite')]
    public function index(EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {
        $userId = 6;
        $user = $userRepository->find($userId);

        $myParties = $entityManager->getRepository(Partie::class)->findBy(['user' => $user]);

        //$parties = $entityManager->getRepository(Partie::class)->findAll();
        $parties = $entityManager->getRepository(Partie::class)->createQueryBuilder('p')
            ->where('p.user != :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        $locationTerrains = $entityManager->getRepository(LocationTerrain::class)->findAll();

        $mesLocationTerrains = $entityManager->getRepository(LocationTerrain::class)->createQueryBuilder('lt')
            ->leftJoin(User::class, 'u', 'WITH', 'lt.user = u.id')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return $this->render('partie/index.html.twig', [

            'parties' => $parties,
            'myParties' => $myParties,
            'locationTerrains' => $locationTerrains,
            'mesLocationTerrains' =>$mesLocationTerrains,
        ]);

    }
    #[Route('/Admin', name: 'app_index_parite_admin')]
    public function indexAdmin(EntityManagerInterface $entityManager): Response
    {
        $parties = $entityManager->getRepository(Partie::class)->findAll();
        $locationTerrains = $entityManager->getRepository(LocationTerrain::class)->findAll();

        return $this->render('partie/indexAdmin.html.twig', [
            'parties' => $parties,
            'locationTerrains' => $locationTerrains,
        ]);
    }
    #[Route('/partie/add', name: 'app_partie_add')]
    public function add(Request $request,userRepository $userRepository): Response
    {
        $userId = 6;
        $user = $userRepository->find($userId);

        $partie = new Partie();
        $form = $this->createForm(PartieFormType::class, $partie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($partie->getCommentaire() === null) {
                $partie->setCommentaire('');
            }
            $partie->setEtat(0);
            $partie->setUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($partie);
            $entityManager->flush();

            return $this->redirectToRoute('app_index_parite');
        }

        return $this->render('partie/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit_partie', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partie $partie): Response
    {
        $form = $this->createForm(PartieFormType::class, $partie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($partie->getCommentaire() === null) {
                $partie->setCommentaire('');
            }
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('app_index_parite');
        }
        return $this->render('partie/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reserve/{id}', name: 'reserve_party')]
    public function reserve(Request $request, EntityManagerInterface $entityManager, Partie $partie,userRepository $userRepository): Response
    {
        $userId = 6;
        $user = $userRepository->find($userId);

        $partie->setEtat(1);

        $locationTerrain = new LocationTerrain();
        $locationTerrain->setPartie($partie);
        $locationTerrain->setUser($user);

        $entityManager->persist($locationTerrain);
        $entityManager->flush();

        return $this->redirectToRoute('app_index_parite');
    }

    #[Route('/partie/delete/{id}', name: 'delete_partie_with_location_front')]
    public function deletePartieWithLocationFront(int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $partieRepository = $entityManager->getRepository(Partie::class);
        $partie = $partieRepository->find($id);

        if (!$partie) {
            throw $this->createNotFoundException('Party not found');
        }

        $locationTerrainRepository = $entityManager->getRepository(LocationTerrain::class);
        $locationTerrains = $locationTerrainRepository->findBy(['partie' => $partie]);

        foreach ($locationTerrains as $locationTerrain) {
            $entityManager->remove($locationTerrain);
        }

        $entityManager->remove($partie);
        $entityManager->flush();

        return $this->redirectToRoute('app_index_parite');
    }



    #[Route('/delete-location-terrain-and-update-partie-etat/{locationTerrainId}/{partyId}', name: 'delete_location_terrain_and_update_partie_etat')]
    public function deleteLocationTerrainAndUpdatePartieEtat(Request $request, int $locationTerrainId, int $partyId): Response
    {
            $entityManager = $this->getDoctrine()->getManager();
            $locationTerrain = $entityManager->getRepository(LocationTerrain::class)->find($locationTerrainId);
            $partie = $entityManager->getRepository(Partie::class)->find($partyId);

            if (!$locationTerrain || !$partie) {
                throw $this->createNotFoundException('LocationTerrain or Partie not found');
            }
            $entityManager->remove($locationTerrain);
            $partie->setEtat(0);
            $entityManager->flush();

            return $this->redirectToRoute('app_index_parite');
    }
    #[Route('/partie/delete/{id}', name: 'delete_partie_with_location')]
    public function deletePartieWithLocation(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $partieRepository = $entityManager->getRepository(Partie::class);
        $partie = $partieRepository->find($id);

        if (!$partie) {
            throw $this->createNotFoundException('Party not found');
        }

        $locationTerrainRepository = $entityManager->getRepository(LocationTerrain::class);
        $locationTerrain = $locationTerrainRepository->findOneBy(['partie' => $partie]);

        if ($locationTerrain) {
            $entityManager->remove($locationTerrain);
            $entityManager->flush();
        }
        $entityManager->remove($partie);
        $entityManager->flush();

        return $this->redirectToRoute('app_index_parite_admin');
    }
}
