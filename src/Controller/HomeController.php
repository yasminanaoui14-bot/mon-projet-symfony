<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PDO;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeController extends AbstractController
{
    private function getPDO(): PDO
{
    return new PDO(
        sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'] ?? '3306',
            $_ENV['DB_NAME']
        ),
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

   #[Route('/', name: 'app_home')]
public function index(): Response

{
$pdo = $this->getPDO();

    $stmtAvis = $pdo->query("SELECT * FROM avis WHERE statut = 'Validé' ORDER BY id DESC");
    $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);

    return $this->render('home/index.html.twig', [
        'avis' => $avis
    ]);
}

    #[Route('/menus', name: 'app_menus')]
    public function menus(Request $request): Response
    {

   $pdo = $this->getPDO();


    $regime = trim((string) $request->query->get('regime', ''));
$personnes = (int) $request->query->get('personnes', 0);
$theme = trim((string) $request->query->get('theme', ''));
$prix = trim((string) $request->query->get('prix', ''));
$sql = 'SELECT * FROM menu WHERE 1 = 1';
$parametres = []; 

if ($regime === 'vegetarien') {
    $sql .= ' AND LOWER(titre) LIKE :regime';
    $parametres['regime'] = '%végétarien%';
}

if ($personnes > 0) {
    $sql .= ' AND nombre_personnes_min <= :personnes';
    $parametres['personnes'] = $personnes;
}
if ($theme !== '') {
    $sql .= ' AND LOWER(CONCAT(titre, " ", theme)) LIKE :theme';
    $parametres['theme'] = '%' . strtolower($theme) . '%';
}

if ($prix === 'moins_100') {
    $sql .= ' AND prix < 100';
}

if ($prix === '100_200') {
    $sql .= ' AND prix BETWEEN 100 AND 200';
}

if ($prix === 'plus_200') {
    $sql .= ' AND prix > 200';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($parametres);

$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

return $this->render('home/menus.html.twig', [
    'menus' => $menus,
    'regimeSelectionne' => $regime,
    'personnesSelectionnees' => $personnes,
    'themeSelectionne' => $theme,
    'prixSelectionne' => $prix,
]);


    $menus = $pdo->query('SELECT * FROM menu')->fetchAll(PDO::FETCH_ASSOC);


    return $this->render('home/menus.html.twig', [
    'menus' => $menus
    ]);
}

#[Route('/contact', name: 'app_contact')]
public function contact(Request $request): Response
{
    if ($request->isMethod('POST')) {
   $pdo = $this->getPDO();

        $stmt = $pdo->prepare(
            'INSERT INTO contact (nom, email, message) VALUES (:nom, :email, :message)'
        );

        $stmt->execute([
            'nom' => $request->request->get('nom'),
            'email' => $request->request->get('email'),
            'message' => $request->request->get('message')
        ]);

       return $this->render('home/contact.html.twig', [
    'success' => true
]);
}

    return $this->render('home/contact.html.twig', [
    'success' => false
]);
}

#[Route('/login', name: 'app_login')]
public function login(Request $request): Response
{
    $erreur = null;

    if ($request->isMethod('POST')) {
        
    $pdo = $this->getPDO();

        $email = $request->request->get('email');
        $motDePasse = $request->request->get('mot_de_passe');

        $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE email = :email');
        $stmt->execute([
            'email' => $email
        ]);

        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($utilisateur && (
            $motDePasse === $utilisateur['mot_de_passe']
                || password_verify($motDePasse, $utilisateur['mot_de_passe'])
        )) {
            $request->getSession()->set('user_id', $utilisateur['id']);
            $request->getSession()->set('nom', $utilisateur['nom']);
            $request->getSession()->set('role', $utilisateur['role']);

            return $this->redirectToRoute('app_home');
        }

        $erreur = 'Email ou mot de passe incorrect.';
    }

    return $this->render('home/login.html.twig', [
        'erreur' => $erreur
    ]);
}

#[Route('/register', name: 'app_register')]
public function register(
    Request $request,
    MailerInterface $mailer
): Response
{
    if ($request->isMethod('POST')) {
        $pdo = $this->getPDO();

        $motDePasse = $request->request->get('mot_de_passe');

        $verification = $pdo->prepare(
        'SELECT id FROM utilisateur WHERE email = :email'
);

        $verification->execute([
        'email' => $request->request->get('email')
]);

        if ($verification->fetch()) {
    return $this->render('home/register.html.twig', [
    'success' => false,
    'error' => null
]);
}

        $stmt = $pdo->prepare(
            'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role)
             VALUES (:nom, :prenom, :email, :mot_de_passe, :role)'
        );

        $stmt->execute([
    'nom' => $request->request->get('nom'),
    'prenom' => $request->request->get('prenom'),
    'email' => $request->request->get('email'),
    'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
    'role' => 'ROLE_USER'
]);

        $email = (new Email())
    ->from('noreply@vitegourmand.fr')
    ->to($request->request->get('email'))
    ->subject('Bienvenue chez Vite & Gourmand !')
    ->html(
        $this->renderView('emails/bienvenue.html.twig', [
            'prenom' => $request->request->get('prenom'),
        ])
    );

$mailer->send($email);

    
return $this->redirectToRoute('app_login');
    }

    return $this->render('home/register.html.twig', [
        'success' => false
    ]);
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
public function commande(
    Request $request,
    MailerInterface $mailer
): Response
{
    $pdo = $this->getPDO();

    if ($request->isMethod('POST')) {
        $stmt = $pdo->prepare("
            INSERT INTO commande 
            (utilisateur_id, menu_id, nom_client, email_client, adresse, telephone, date_prestation, heure_prestation, nombre_personnes, statut, prix_total)
            VALUES
            (:utilisateur_id, :menu_id, :nom_client, :email_client, :adresse, :telephone, :date_prestation, :heure_prestation, :nombre_personnes, 'En attente', :prix_total)
        ");

        $stmt->execute([
            'utilisateur_id' => $request->getSession()->get('user_id'),
            'menu_id' => $request->request->get('menu_id'),
            'nom_client' => $request->request->get('nom_client'),
            'email_client' => $request->request->get('email_client'),
            'adresse' => $request->request->get('adresse'),
            'telephone' => $request->request->get('telephone'),
            'date_prestation' => $request->request->get('date_prestation'),
            'heure_prestation' => $request->request->get('heure_prestation'),
            'nombre_personnes' => $request->request->get('nombre_personnes'),
            'prix_total' => 0
        ]);

        $email = (new Email())
            ->from('noreply@vitegourmand.fr')
            ->to($request->request->get('email_client'))
            ->subject('Confirmation de votre commande')
            ->html(
        $this->renderView('emails/commande.html.twig', [
            'nom' => $request->request->get('nom_client'),
            'date' => $request->request->get('date_prestation'),
            'heure' => $request->request->get('heure_prestation'),
            'personnes' => $request->request->get('nombre_personnes'),
        ])
    );

        $mailer->send($email);

    return $this->redirectToRoute('app_mon_compte');
    }

    return $this->render('home/commande.html.twig');
}



#[Route('/rapport', name: 'app_rapport')]
public function rapport(Request $request): Response
{
    if ($request->getSession()->get('role') !== 'ROLE_ADMIN') {
        return $this->redirectToRoute('app_login');
    }

    $pdo = $this->getPDO();

    $menu = trim((string) $request->query->get('menu', ''));
    $dateDebut = trim((string) $request->query->get('date_debut', ''));
    $dateFin = trim((string) $request->query->get('date_fin', ''));

    $sql = "
        SELECT
            m.titre AS menu,
            COUNT(c.id) AS nombre_commandes,
            COALESCE(SUM(m.prix * c.nombre_personnes), 0) AS chiffre_affaires
        FROM menu m
        LEFT JOIN commande c ON c.menu_id = m.id
        WHERE 1 = 1
    ";

    $parametres = [];

    if ($menu !== '') {
        $sql .= " AND m.titre = :menu";
        $parametres['menu'] = $menu;
    }

    if ($dateDebut !== '') {
        $sql .= " AND c.date_prestation >= :date_debut";
        $parametres['date_debut'] = $dateDebut;
    }

    if ($dateFin !== '') {
        $sql .= " AND c.date_prestation <= :date_fin";
        $parametres['date_fin'] = $dateFin;
    }

    $sql .= " GROUP BY m.id, m.titre ORDER BY nombre_commandes DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametres);

    $statistiques = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalCommandes = 0;
    $chiffreAffaires = 0;
    $menuPlusCommande = 'Aucune donnée';

    foreach ($statistiques as $index => $statistique) {
        $totalCommandes += (int) $statistique['nombre_commandes'];
        $chiffreAffaires += (float) $statistique['chiffre_affaires'];

        if ($index === 0 && (int) $statistique['nombre_commandes'] > 0) {
            $menuPlusCommande = $statistique['menu'];
        }
    }

    $panierMoyen = $totalCommandes > 0
        ? $chiffreAffaires / $totalCommandes
        : 0;

    $menus = $pdo
        ->query("SELECT titre FROM menu ORDER BY titre ASC")
        ->fetchAll(PDO::FETCH_ASSOC);

    return $this->render('home/rapport.html.twig', [
        'statistiques' => $statistiques,
        'totalCommandes' => $totalCommandes,
        'chiffreAffaires' => $chiffreAffaires,
        'menuPlusCommande' => $menuPlusCommande,
        'panierMoyen' => $panierMoyen,
        'menus' => $menus,
        'menuSelectionne' => $menu,
        'dateDebut' => $dateDebut,
        'dateFin' => $dateFin,
    ]);
}

#[Route('/employe', name: 'app_employe')]
public function employe(
    Request $request,
    MailerInterface $mailer
): Response
{

    $pdo = $this->getPDO();

    $action = '';
    if ($request->isMethod('POST')) {
    $action = $request->request->get('action');

    if ($action === 'avis') {
    $avisId = (int) $request->request->get('avis_id');
    $decision = (string) $request->request->get('decision');

    if (in_array($decision, ['Validé', 'Refusé'], true)) {
        $stmtAvis = $pdo->prepare("
            UPDATE avis
            SET statut = :statut
            WHERE id = :id
        ");

        $stmtAvis->execute([
            'statut' => $decision,
            'id' => $avisId,
        ]);
    }

    return $this->redirectToRoute('app_employe');
}

    if ($action === 'commande') {
        $commandeId = (int) $request->request->get('commande_id');
        $nouveauStatut = (string) $request->request->get('statut');

        $stmtCommande = $pdo->prepare("
            SELECT
                nom_client,
                email_client,
                date_prestation,
                heure_prestation,
                nombre_personnes
            FROM commande
            WHERE id = :id
        ");

        $stmtCommande->execute([
            'id' => $commandeId,
        ]);

        $commande = $stmtCommande->fetch(PDO::FETCH_ASSOC);

        if ($commande) {
            $stmtUpdate = $pdo->prepare("
                UPDATE commande
                SET statut = :statut
                WHERE id = :id
            ");

            $stmtUpdate->execute([
                'statut' => $nouveauStatut,
                'id' => $commandeId,
            ]);

            if ($nouveauStatut === 'Acceptée') {
                $email = (new Email())
                    ->from('noreply@vitegourmand.fr')
                    ->to($commande['email_client'])
                    ->subject('Votre commande a été acceptée')
                    ->html(
                        $this->renderView('emails/commande_validee.html.twig', [
                            'nom' => $commande['nom_client'],
                            'date' => $commande['date_prestation'],
                            'heure' => $commande['heure_prestation'],
                            'personnes' => $commande['nombre_personnes'],
                        ])
                    );

                $mailer->send($email);
            }

            if ($nouveauStatut === 'Refusée') {
                $email = (new Email())
                    ->from('noreply@vitegourmand.fr')
                    ->to($commande['email_client'])
                    ->subject('Votre commande n’a pas pu être acceptée')
                    ->html(
                        $this->renderView('emails/commande_refusee.html.twig', [
                            'nom' => $commande['nom_client'],
                            'date' => $commande['date_prestation'],
                            'heure' => $commande['heure_prestation'],
                            'personnes' => $commande['nombre_personnes'],
                            'motif' => null,
                        ])
                    );

                $mailer->send($email);
            }
        }

        return $this->redirectToRoute('app_employe');
    }
}

if ($action === 'annuler') {
    $commandeId = (int) $request->request->get('commande_id');
    $motif = trim((string) $request->request->get('motif_annulation'));

    $stmtCommande = $pdo->prepare("
        SELECT
            nom_client,
            email_client,
            date_prestation,
            heure_prestation,
            nombre_personnes
        FROM commande
        WHERE id = :id
    ");

    $stmtCommande->execute([
        'id' => $commandeId,
    ]);

    $commande = $stmtCommande->fetch(PDO::FETCH_ASSOC);

    if ($commande) {
        $stmtUpdate = $pdo->prepare("
            UPDATE commande
            SET statut = 'Annulée'
            WHERE id = :id
        ");

        $stmtUpdate->execute([
            'id' => $commandeId,
        ]);

        $email = (new Email())
            ->from('noreply@vitegourmand.fr')
            ->to($commande['email_client'])
            ->subject('Votre commande a été annulée')
            ->html(
                $this->renderView('emails/commande_refusee.html.twig', [
                    'nom' => $commande['nom_client'],
                    'date' => $commande['date_prestation'],
                    'heure' => $commande['heure_prestation'],
                    'personnes' => $commande['nombre_personnes'],
                    'motif' => $motif,
                ])
            );

        $mailer->send($email);
    }

    return $this->redirectToRoute('app_employe');
}

    $clientRecherche = trim((string) $request->query->get('client', ''));
$statutRecherche = trim((string) $request->query->get('statut', ''));

$sql = "
    SELECT c.id,
           c.nom_client,
           c.email_client,
           c.date_prestation,
           c.statut,
           m.titre AS menu
    FROM commande c
    LEFT JOIN menu m ON c.menu_id = m.id
    WHERE 1 = 1
";

$parametres = [];

if ($clientRecherche !== '') {
    $sql .= "
        AND (
            c.nom_client LIKE :client
            OR c.email_client LIKE :client
        )
    ";

    $parametres['client'] = '%' . $clientRecherche . '%';
}

if ($statutRecherche !== '') {
    $sql .= " AND c.statut = :statut ";
    $parametres['statut'] = $statutRecherche;
}

$sql .= " ORDER BY c.date_prestation ASC ";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametres);

$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtAvis = $pdo->query("
    SELECT *
    FROM avis
    ORDER BY id DESC
");

    $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);

    return $this->render('home/employe.html.twig', [
    'commandes' => $commandes,
    'avis' => $avis
]);

}

#[Route('/menu-vegetarien', name: 'app_menu_vegetarien')]
public function menuVegetarien(): Response
{
    return $this->render('home/menu_vegetarien.html.twig');
}

#[Route('/admin', name: 'app_admin')]
public function admin(
    Request $request,
    MailerInterface $mailer
): Response

{
    if ($request->getSession()->get('role') !== 'ROLE_ADMIN') {
        return $this->redirectToRoute('app_login');
    }

    $pdo = $this->getPDO();

    $message = null;
    $error = null;

    if ($request->isMethod('POST')) {
        $action = (string) $request->request->get('action');

        if ($action === 'creer_employe') {
            $emailEmploye = trim((string) $request->request->get('email'));
            $motDePasse = (string) $request->request->get('mot_de_passe');

            if (!filter_var($emailEmploye, FILTER_VALIDATE_EMAIL)) {
                $error = 'Veuillez saisir une adresse e-mail valide.';
            } elseif (strlen($motDePasse) < 10) {
                $error = 'Le mot de passe doit contenir au moins 10 caractères.';
            } else 
            {
                $verification = $pdo->prepare(
                    'SELECT id FROM utilisateur WHERE email = :email'
                );

                $verification->execute([
                    'email' => $emailEmploye,
                ]);

                if ($verification->fetch()) {
                    $error = 'Cette adresse e-mail est déjà utilisée.';
                } else 
                {
                    $stmt = $pdo->prepare("
                        INSERT INTO utilisateur
                            (nom, prenom, email, mot_de_passe, role)
                        VALUES
                            (:nom, :prenom, :email, :mot_de_passe, :role)
                    ");

                    $stmt->execute([
                        'nom' => 'Employé',
                        'prenom' => 'Nouveau',
                        'email' => $emailEmploye,
                        'mot_de_passe' => password_hash(
                            $motDePasse,
                            PASSWORD_DEFAULT
                        ),
                        'role' => 'ROLE_EMPLOYE',
                    ]);

                    $email = (new Email())
                        ->from('noreply@vitegourmand.fr')
                        ->to($emailEmploye)
                        ->subject('Création de votre compte employé')
                        ->html(
                            $this->renderView(
                                'emails/compte_employe.html.twig',
                                [
                                    'emailEmploye' => $emailEmploye,
                                ]
                            )
                        );

                    $mailer->send($email);

                    $message = 'Le compte employé a été créé avec succès.';
                }
            }
        }
                    if ($action === 'desactiver_employe') {
            $employeId = (int) $request->request->get('employe_id');

            $stmtDesactivation = $pdo->prepare("
                UPDATE utilisateur
                SET role = 'ROLE_DESACTIVE'
                WHERE id = :id
                AND role = 'ROLE_EMPLOYE'
            ");

            $stmtDesactivation->execute([
                'id' => $employeId,
            ]);

            return $this->redirectToRoute('app_admin');
        }
    }

    $stmtEmployes = $pdo->query("
        SELECT id, nom, prenom, email, role
        FROM utilisateur
        WHERE role IN ('ROLE_EMPLOYE', 'ROLE_DESACTIVE')
        ORDER BY id DESC
    ");

    $employes = $stmtEmployes->fetchAll(PDO::FETCH_ASSOC);

    return $this->render('home/admin.html.twig', [
        'employes' => $employes,
        'message' => $message,
        'error' => $error,
    ]);
}

#[Route('/admin-auto-login', name: 'app_admin_auto_login')]
public function adminAutoLogin(Request $request): Response
{
    $request->getSession()->set('user_id', 3);
    $request->getSession()->set('nom', 'Admin');
    $request->getSession()->set('role', 'ROLE_ADMIN');

    return $this->redirectToRoute('app_admin');
}

#[Route('/logout', name: 'app_logout')]
public function logout(Request $request): Response
{
    $request->getSession()->clear();

    return $this->redirectToRoute('app_home');
}

#[Route('/mon-compte', name: 'app_mon_compte')]
public function monCompte(Request $request): Response
{
    $pdo = $this->getPDO();

        if ($request->isMethod('POST')) {
    $action = $request->request->get('action');

    if ($action === 'annuler') {
        $commandeId = $request->request->get('commande_id');

        $stmt = $pdo->prepare("
            UPDATE commande
            SET statut = 'Annulée'
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $commandeId
        ]);

        return $this->redirectToRoute('app_mon_compte');
    }

    if ($action === 'avis') {
        $note = $request->request->get('note');
        $commentaire = $request->request->get('commentaire');

        $stmt = $pdo->prepare("
            INSERT INTO avis (client, note, commentaire, statut)
            VALUES ('Utilisateur', :note, :commentaire, 'En attente')
        ");

        $stmt->execute([
            'note' => $note,
            'commentaire' => $commentaire
        ]);

        return $this->redirectToRoute('app_mon_compte');
    }
}


    $stmt = $pdo->prepare("
    SELECT c.id, c.date_prestation, c.statut, m.titre AS menu
    FROM commande c
    LEFT JOIN menu m ON c.menu_id = m.id
    ORDER BY c.date_prestation DESC
");

    $stmt->execute();

    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $this->render('home/mon_compte.html.twig', [
        'commandes' => $commandes
    ]);
}


#[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
public function forgotPassword(
    Request $request,
    MailerInterface $mailer
): Response {
    $message = null;
    $error = null;

    if ($request->isMethod('POST')) {
        $emailUtilisateur = trim((string) $request->request->get('email'));

        if (!filter_var($emailUtilisateur, FILTER_VALIDATE_EMAIL)) {
            $error = 'Veuillez saisir une adresse e-mail valide.';
        } else {
            $pdo = $this->getPDO();

            $stmt = $pdo->prepare(
                'SELECT id, email
                 FROM utilisateur
                 WHERE email = :email'
            );

            $stmt->execute([
                'email' => $emailUtilisateur
            ]);

            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

            $message = 'Si cette adresse existe, un lien de réinitialisation a été envoyé.';

            if ($utilisateur) {
                $token = bin2hex(random_bytes(32));
                $expiration = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');

                $pdo->prepare(
                    'DELETE FROM password_reset
                     WHERE utilisateur_id = :utilisateur_id'
                )->execute([
                    'utilisateur_id' => $utilisateur['id']
                ]);

                $stmt = $pdo->prepare(
                    'INSERT INTO password_reset
                        (utilisateur_id, token, expires_at)
                     VALUES
                        (:utilisateur_id, :token, :expires_at)'
                );

                $stmt->execute([
                    'utilisateur_id' => $utilisateur['id'],
                    'token' => $token,
                    'expires_at' => $expiration
                ]);

                $lien = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $email = (new Email())
                    ->from('noreply@vitegourmand.fr')
                    ->to($utilisateur['email'])
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html(
                        $this->renderView('emails/reset_password.html.twig', [
                            'lien' => $lien
                        ])
                    );

        $mailer->send($email);
            }
        }
    }

    return $this->render('home/forgot_password.html.twig', [
        'message' => $message,
        'error' => $error
    ]);
}

#[Route('/reset-password/{token}', name: 'app_reset_password')]
public function resetPassword(
    string $token,
    Request $request
): Response {
    
    $pdo = $this->getPDO();

    $stmt = $pdo->prepare(
        'SELECT pr.id, pr.utilisateur_id, pr.expires_at, pr.utilise
         FROM password_reset pr
         WHERE pr.token = :token'
    );

    $stmt->execute([
        'token' => $token
    ]);

    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$reset ||
        $reset['utilise'] == 1 ||
        strtotime($reset['expires_at']) < time()
    ) {
        return $this->render('home/reset_password.html.twig', [
            'error' => 'Ce lien est invalide ou a expiré.',
            'success' => null
        ]);
    }

    $error = null;
    $success = null;

    if ($request->isMethod('POST')) {
        $motDePasse = (string) $request->request->get('mot_de_passe');
        $confirmation = (string) $request->request->get('confirmation');

        if (strlen($motDePasse) < 10) {
            $error = 'Le mot de passe doit contenir au moins 10 caractères.';
        } elseif ($motDePasse !== $confirmation) {
            $error = 'Les deux mots de passe ne correspondent pas.';
        } else {
            $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'UPDATE utilisateur
                 SET mot_de_passe = :mot_de_passe
                 WHERE id = :id'
            );

            $stmt->execute([
                'mot_de_passe' => $motDePasseHash,
                'id' => $reset['utilisateur_id']
            ]);

            $stmt = $pdo->prepare(
                'UPDATE password_reset
                 SET utilise = 1
                 WHERE id = :id'
            );

            $stmt->execute([
                'id' => $reset['id']
            ]);

            $success = 'Votre mot de passe a été modifié avec succès.';
        }
    }

    return $this->render('home/reset_password.html.twig', [
        'error' => $error,
        'success' => $success
    ]);
}

}
