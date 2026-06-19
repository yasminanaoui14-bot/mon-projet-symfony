<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

#[Route('/menus', name: 'app_menus')]
public function menus(): Response
{
    return $this->render('home/menus.html.twig');
}

#[Route('/contact', name: 'app_contact')]
public function contact(): Response
{
    return $this->render('home/contact.html.twig');
}

#[Route('/login', name: 'app_login')]
public function login(): Response
{
    return $this->render('home/login.html.twig');
}

#[Route('/register', name: 'app_register')]
public function register(): Response
{
    return $this->render('home/register.html.twig');
}

#[Route('/menu', name: 'app_detail')]
public function detail(): Response
{
    return $this->render('home/detail.html.twig');
}

#[Route('/menu-classique', name: 'app_menu_classique')]
public function menuClassique(): Response
{
    return $this->render('home/menu_classique.html.twig');
}

#[Route('/menu-festif', name: 'app_menu_festif')]
public function menuFestif(): Response
{
    return $this->render('home/menu_festif.html.twig');
}

#[Route('/commande', name: 'app_commande')]
public function commande(): Response
{
    return $this->render('home/commande.html.twig');
}

#[Route('/rapport', name: 'app_rapport')]
public function rapport(): Response
{
    return $this->render('home/rapport.html.twig');
}

#[Route('/employe', name: 'app_employe')]
public function employe(Request $request, SessionInterface $session): Response
{
    if (!$session->has('commandes')) {
        $session->set('commandes', [
            1 => [
                'numero' => '#001',
                'client' => 'Yasmina Nao',
                'email' => 'yasmina@example.com',
                'menu' => 'Menu Classique',
                'date' => '20/07/2026',
                'statut' => 'Acceptée'
            ],
            2 => [
                'numero' => '#002',
                'client' => 'Florian Clau',
                'email' => 'florian@example.com',
                'menu' => 'Menu Festif',
                'date' => '22/07/2026',
                'statut' => 'En préparation'
            ],
            3 => [
                'numero' => '#003',
                'client' => 'Riad Inao',
                'email' => 'riad@example.com',
                'menu' => 'Menu Végétarien',
                'date' => '25/07/2026',
                'statut' => 'En cours de livraison'
            ],
        ]);
    }

    $commandes = $session->get('commandes');

    if (!$session->has('avis')) {
    $session->set('avis', [
        1 => [
            'client' => 'Yasmina Nao',
            'note' => '⭐⭐⭐⭐⭐',
            'commentaire' => 'Excellent service, menus délicieux.',
            'statut' => 'En attente'
        ],
        2 => [
            'client' => 'Florian Clau',
            'note' => '⭐⭐⭐⭐',
            'commentaire' => 'Livraison rapide et bonne qualité.',
            'statut' => 'En attente'
        ]
    ]);
}

$avis = $session->get('avis');

    if ($request->isMethod('POST')) {
        $id = $request->request->get('commande_id');
        $nouveauStatut = $request->request->get('statut');

        $action = $request->request->get('action');

if ($action === 'avis') {
    $avisId = $request->request->get('avis_id');
    $decision = $request->request->get('decision');

    if (isset($avis[$avisId])) {
        $avis[$avisId]['statut'] = $decision;
        $session->set('avis', $avis);
    }
}

        if (isset($commandes[$id])) {
            $commandes[$id]['statut'] = $nouveauStatut;
            $session->set('commandes', $commandes);
        }
    }

    $filtreStatut = $request->query->get('statut');
    $recherche = $request->query->get('recherche');

    $commandesFiltrees = array_filter($commandes, function ($commande) use ($filtreStatut, $recherche) {
        $okStatut = !$filtreStatut || $filtreStatut === 'Tous' || $commande['statut'] === $filtreStatut;
        $okRecherche = !$recherche || stripos($commande['client'], $recherche) !== false || stripos($commande['email'], $recherche) !== false;

        return $okStatut && $okRecherche;
    });

        return $this->render('home/employe.html.twig', [
     'commandes' => $commandesFiltrees,
      'avis' => $avis
    ]);
}



#[Route('/menu-vegetarien', name: 'app_menu_vegetarien')]
public function menuVegetarien(): Response
{
    return $this->render('home/menu_vegetarien.html.twig');
}
}
