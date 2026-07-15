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
   #[Route('/', name: 'app_home')]
public function index(): Response

{

    $pdo = new PDO(
    'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4',
    'root',
    ''
);

    $stmtAvis = $pdo->query("SELECT * FROM avis WHERE statut = 'Validé' ORDER BY id DESC");
    $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);

    return $this->render('home/index.html.twig', [
        'avis' => $avis
    ]);
}

    #[Route('/menus', name: 'app_menus')]
    public function menus(Request $request): Response
    {

    $pdo = new PDO(
        'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4',
        'root',
        ''
    );

    if ($request->isMethod('POST')) {
    $id = $request->request->get('commande_id');
    $statut = $request->request->get('statut');

    $stmt = $pdo->prepare('UPDATE commande SET statut = :statut WHERE id = :id');
    $stmt->execute([
        'statut' => $statut,
        'id' => $id
    ]);
}

    $menus = $pdo->query('SELECT * FROM menu')->fetchAll(PDO::FETCH_ASSOC);


    return $this->render('home/menus.html.twig', [
    'menus' => $menus
    ]);
}

#[Route('/contact', name: 'app_contact')]
public function contact(Request $request): Response
{
    if ($request->isMethod('POST')) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4',
            'root',
            ''
        );

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
        
    $pdo = new PDO(
    'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4',
    'root',
    ''
);

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
public function register(Request $request): Response
{
    if ($request->isMethod('POST')) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4',
            'root',
            ''
        );

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
public function commande(Request $request): Response
{
    $pdo = new PDO(
        'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4',
        'root',
        ''
    );

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

        return $this->redirectToRoute('app_mon_compte');
    }

    return $this->render('home/commande.html.twig');
}



#[Route('/rapport', name: 'app_rapport')]
public function rapport(): Response
{
    return $this->render('home/rapport.html.twig');
}

#[Route('/employe', name: 'app_employe')]
public function employe(Request $request): Response
{
    $pdo = new PDO(
        'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4',
        'root',
        ''
    );

    $stmt = $pdo->query("
        SELECT c.id,
               c.nom_client,
               c.email_client,
               c.date_prestation,
               c.statut,
               m.titre AS menu
        FROM commande c
        LEFT JOIN menu m ON c.menu_id = m.id
        ORDER BY c.date_prestation ASC
    ");

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
public function admin(Request $request): Response
{
    if ($request->getSession()->get('role') !== 'ROLE_ADMIN') {
    return $this->redirectToRoute('app_login');
}
return $this->render('home/admin.html.twig');
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
    $pdo = new PDO( 'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4','root',''
    );

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
            $pdo = new PDO(
                'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4',
                'root',
                ''
            );

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
                    ->from('no-reply@vite-gourmand.test')
                    ->to($utilisateur['email'])
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html(
                        '<h2>Réinitialisation du mot de passe</h2>
                        <p>Cliquez sur le lien suivant :</p>
                        <p><a href="' . $lien . '">Réinitialiser mon mot de passe</a></p>
                        <p>Ce lien expire dans une heure.</p>'
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
    $pdo = new PDO(
        'mysql:host=localhost;dbname=vite_gourmand;charset=utf8mb4',
        'root',
        ''
    );

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
