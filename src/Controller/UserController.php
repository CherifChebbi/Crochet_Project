<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/dashboard/users', name: 'app_dashboard_user')]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        // Récupérer les paramètres de recherche et de filtre
        $searchQuery = $request->query->get('search_query');
        $roleFilter = $request->query->get('role_filter');
        
        // Récupérer les statistiques
        $totalUsers = $userRepository->count([]); // Nombre total d'utilisateurs
        $totalAdmins = $userRepository->countUsersByRole('ROLE_ADMIN'); // Nombre d'administrateurs
        $totalSimpleUsers = $userRepository->countUsersByRole('ROLE_USER'); // Nombre d'utilisateurs 

        // Utiliser le repository pour rechercher et filtrer les utilisateurs
        $users = $userRepository->findBySearchAndRole($searchQuery, $roleFilter);

        return $this->render('back/users.html.twig', [
            'users' => $users,
            'search_query' => $searchQuery,
            'role_filter' => $roleFilter,
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalSimpleUsers' => $totalSimpleUsers,
        ]);
    }
    //---------DELETE SIMPLE back----------------
    #[Route('/user/{id}/delete', name: 'app_dashboard_user_delete')]
    public function deletePays( User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard_user');
    }
    
    #[Route('/user/{id}/edit', name: 'app_dashboard_user_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Créer le formulaire pour l'utilisateur
        $form = $this->createForm(UserType::class, $user);
    
        // Gérer la demande de formulaire
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les rôles sélectionnés depuis le formulaire
            $roles = $form->get('roles')->getData();
        
            // Normaliser les données pour éviter les indices numériques
            if (is_array($roles)) {
                $roles = array_values($roles);  // Supprime les clés numériques éventuelles
            }
        
            // Définir les rôles dans l'entité utilisateur
            $user->setRoles($roles);
        
            // Sauvegarder les modifications
            $entityManager->persist($user);
            $entityManager->flush();
        
            // Redirection
            return $this->redirectToRoute('app_dashboard_user', ['id' => $user->getId()]);
        }
    
        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}
