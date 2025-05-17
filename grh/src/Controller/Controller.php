<?php
namespace App\Controller;

use App\Repository\CandidatureRepository;
use App\Repository\ContratRepository;
use App\Repository\EmployeRepository;
use App\Repository\TimesheetRepository;
use App\Repository\DemandeCongeRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Candidature;
use App\Entity\Contrat;
use App\Entity\Employe;
use App\Entity\Timesheet;
use App\Entity\DemandeConge;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Controller extends AbstractController{
    public function employe(EmployeRepository $employeRepository) 
    {
        $employes=$employeRepository->findAll();
        return $this->render('employe.html.twig', ["employe"=>$employes]) ;
    }
    public function ajout()
    {       
        return $this->render('ajout.html.twig');
    }
    public function ajoutEmploye(EntityManagerInterface $entityManager, Request $request)
    {       
    
    $nom = $request->request->get("nom");
    $prenom = $request->request->get("prenom");
    $email = $request->request->get("email");
    $poste = $request->request->get("poste");
    $salaire = $request->request->get("salaire");
    $date = $request->request->get("date");
    

    $errors = [];

    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }
    if (empty($prenom)) {
        $errors[] = "Le prenom est requis.";
    }
    if (empty($email)) {
        $errors[] = "L'email' est requis.";
    }
    if (empty($poste)) {
        $errors[] = "Le poste est requis.";
    }
    if (empty($salaire)) {
        $errors[] = "Le salaire est requis.";
    }
    if (empty($date)) {
        $errors[] = "La date d'embauche est requise.";
    }

    if (!empty($errors)) {
        return $this->render('erreurEmploye.html.twig', [
            'errors' => $errors,
        ]);
    }

    $employe = new Employe();
    $employe->setNom($nom);
    $employe->setPrenom($prenom);
    $employe->setEmail($email);
    $employe->setPoste($poste);
    $employe->setSalaire($salaire);
    $employe->setDateEmbauche($date);
    
    $entityManager->persist($employe);
    $entityManager->flush();

    return $this->render('ajoutEmploye.html.twig');
    }
    public function erreur()
    {       
        return $this->render('erreur.html.twig');
    }

    public function modifier(int $id, EntityManagerInterface $entityManager,Request $request)
    {    
         $employe=$entityManager->getRepository(Employe ::class)->find($id);
         return $this->render('modifier.html.twig', ['employe' => $employe]);
    }
    public function modifierEmploye(EntityManagerInterface $entityManager, Request $request)
    {

    $id = $request->request->get("id");
    $employe = $entityManager->getRepository(Employe::class)->find($id);

    
    $nom = $request->request->get("nom");
    $prenom = $request->request->get("prenom");
    $email = $request->request->get("email");
    $poste = $request->request->get("poste");
    $salaire = $request->request->get("salaire");
    $date = $request->request->get("date");
    
    
    $errors = [];

    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }
    if (empty($prenom)) {
        $errors[] = "Le prenom est requis.";
    }
    if (empty($email)) {
        $errors[] = "L'email' est requis.";
    }
    if (empty($poste)) {
        $errors[] = "Le poste est requis.";
    }
    if (empty($salaire)) {
        $errors[] = "Le salaire est requis.";
    }
    if (empty($date)) {
        $errors[] = "La date d'embauche est requise.";
    }

    if (!empty($errors)) {
        return $this->render('erreurEmploye.html.twig', [
            'errors' => $errors,
        ]);
    }

    $employe->setNom($nom);
    $employe->setPrenom($prenom);
    $employe->setEmail($email);
    $employe->setPoste($poste);
    $employe->setSalaire($salaire);
    $employe->setDateEmbauche($date);
    
    $entityManager->flush();
    
    return $this->redirect('/employe');
}

    // public function supprimer(int $id, EntityManagerInterface $entityManager, Request $request)
    // {
    //     $employe = $entityManager->getRepository(Employe::class)->find($id);
    //     $entityManager->remove($employe);
    //     $entityManager->flush();
    //     return $this->redirect('/employe');
    // }
    public function supprimer(int $id, EntityManagerInterface $entityManager, Request $request)
    {
        $employe = $entityManager->getRepository(Employe::class)->find($id);

        if (!$employe) {
            throw $this->createNotFoundException(
                'No employee found for id ' . $id
            );
        }

        if ($this->isCsrfTokenValid('delete' . $employe->getId(), $request->request->get('_token'))) {
            $entityManager->remove($employe);
            $entityManager->flush();

            $this->addFlash('success', 'Employé supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        $employes = $entityManager->getRepository(Employe::class)->findAll();
        return $this->render('employe.html.twig', ["employe" => $employes]);
    }
    public function contrat(ContratRepository $contratRepository)
{
    $contrats = $contratRepository->findAll();

    $contratsAvecHistorique = [];

    foreach ($contrats as $contrat) {
        $historique = [];
        if ($contrat->getHistorique()) {
            $lignes = explode("\n", $contrat->getHistorique());
            foreach ($lignes as $ligne) {
                $modification = json_decode($ligne, true);
                if ($modification) {
                    // Normaliser les valeurs imbriquées en chaînes JSON
                    foreach ($modification as $key => $value) {
                        if (is_array($value)) {
                            $modification[$key] = json_encode($value);
                        }
                    }
                    $historique[] = $modification;
                }
            }
        }

        $contratsAvecHistorique[] = [
            'contrat' => $contrat,
            'historique' => $historique,
        ];
    }

    return $this->render('contrat.html.twig', [
        'contratsAvecHistorique' => $contratsAvecHistorique,
    ]);
}

    
    public function ajoutContrat(EmployeRepository $employeRepository): Response
    {
        $employes = $employeRepository->findAll();

        return $this->render('ajoutContrat.html.twig', ['employes' => $employes]);
    }
    public function ajouterContrat(EntityManagerInterface $entityManager, Request $request, EmployeRepository $employeRepository)
    {       
    
    $type = $request->request->get("type");
    $dateDebut = $request->request->get("dateDebut");
    $dateFin = $request->request->get("dateFin");
    $salaire = $request->request->get("salaire");
    $employeId = $request->request->get("employe");
    $AvantagesSociaux = $request->request->get("AvantagesSociaux");

    $employe = $employeRepository->find($employeId);

    $errors = [];

    if (empty($type)) {
        $errors[] = "Le type est requis.";
    }
    if (empty($dateDebut)) {
        $errors[] = "La date du début est requise.";
    }
    if (empty($salaire)) {
        $errors[] = "Le salaire est requis.";
    }
    if (empty($AvantagesSociaux)) {
        $errors[] = "Les Avantages Sociaux sont requises.";
    }

    if (!empty($errors)) {
        return $this->render('erreurContrat.html.twig', [
            'errors' => $errors,
        ]);
    }

    $contrat = new Contrat();
    $contrat->setType($type);
    $contrat->setDateDebut($dateDebut);
    $contrat->setDateFin($dateFin);
    $contrat->setSalaire($salaire);
    $contrat->setEmploye($employe);
    $contrat->setAvantagesSociaux($AvantagesSociaux);

    $entityManager->persist($contrat);
    $entityManager->flush();

    return $this->render('ajouterContrat.html.twig');
    }

    public function modifierContrat(int $id, EntityManagerInterface $entityManager, Request $request, EmployeRepository $employeRepository)
    {
        $contrat = $entityManager->getRepository(Contrat::class)->find($id);
        $employes = $employeRepository->findAll(); // Récupérez la liste des employés
    
        return $this->render('modifierContrat.html.twig', [
            'contrat' => $contrat,
            'employes' => $employes, // Passez la liste des employés au template
        ]);
    }

//     public function modificationContrat(EntityManagerInterface $entityManager, Request $request, ContratRepository $contratRepository, EmployeRepository $employeRepository)
//     {
//     $id = $request->request->get("id");
//     $contrat = $entityManager->getRepository(Contrat::class)->find($id);

//     // Récupération de la liste des employés
//     $employes = $employeRepository->findAll();

//     $type = $request->request->get("type");
//     $dateDebut = $request->request->get("dateDebut");
//     $dateFin = $request->request->get("dateFin");
//     $salaire = $request->request->get("salaire");
//     $AvantagesSociaux = $request->request->get("AvantagesSociaux");
//     $employeId = $request->request->get("employe");

//     $errors = [];

//     if (empty($type)) {
//         $errors[] = "Le type est requis.";
//     }
//     if (empty($AvantagesSociaux)) {
//         $errors[] = "Les Avantages Sociaux sont requises.";
//     }
//     if (empty($dateDebut)) {
//         $errors[] = "La date du début est requise.";
//     }
//     if (empty($salaire)) {
//         $errors[] = "Le salaire est requis.";
//     }

//     if (!empty($errors)) {
//         return $this->render('erreurContrat.html.twig', [
//             'errors' => $errors,
//         ]);
//     }

//     // Recherche de l'employé sélectionné
//     $employe = $employeRepository->find($employeId);

//     $contrat->setType($type);
//     $contrat->setDateDebut($dateDebut);
//     $contrat->setDateFin($dateFin);
//     $contrat->setSalaire($salaire);
//     $contrat->setAvantagesSociaux($AvantagesSociaux);
//     $contrat->setEmploye($employe);

//     $entityManager->flush();

//     return $this->redirect('/contrat');
// }
public function modificationContrat(EntityManagerInterface $entityManager, Request $request, ContratRepository $contratRepository, EmployeRepository $employeRepository, SerializerInterface $serializer)
{
    $id = $request->request->get("id");
    $contrat = $entityManager->getRepository(Contrat::class)->find($id);

    // Récupération de la liste des employés
    $employes = $employeRepository->findAll();

    $type = $request->request->get("type");
    $dateDebut = $request->request->get("dateDebut");
    $dateFin = $request->request->get("dateFin");
    $salaire = $request->request->get("salaire");
    $avantagesSociaux = $request->request->get("AvantagesSociaux");
    $employeId = $request->request->get("employe");

    $errors = [];

    if (empty($type)) {
        $errors[] = "Le type est requis.";
    }
    if (empty($dateDebut)) {
        $errors[] = "La date du début est requise.";
    }
    if (empty($salaire)) {
        $errors[] = "Le salaire est requis.";
    }

    if (!empty($errors)) {
        return $this->render('erreurContrat.html.twig', [
            'errors' => $errors,
        ]);
    }

    // Récupération de l'employé sélectionné
    $employe = $employeRepository->find($employeId);

    // Enregistrement de l'ancienne version
    $ancienneVersion = [
        'type' => $contrat->getType(),
        'dateDebut' => $contrat->getDateDebut(),
        'dateFin' => $contrat->getDateFin(),
        'salaire' => $contrat->getSalaire(),
        'avantagesSociaux' => $contrat->getAvantagesSociaux(),
        'employe' => $contrat->getEmploye() ? $contrat->getEmploye()->getNom() : null,
        'dateModification' => (new \DateTime())->format('Y-m-d H:i:s'),
    ];

    // Transformation de l'ancienne version en JSON
    $encoders = [new JsonEncoder()];
    $normalizers = [new ObjectNormalizer()];
    $serializer = new Serializer($normalizers, $encoders);
    $ancienneVersionJson = $serializer->serialize($ancienneVersion, 'json');

    // Récupération de l'historique actuel
    $historiqueActuel = $contrat->getHistorique();

    // Ajout de la nouvelle modification à l'historique
    $nouvelHistorique = $historiqueActuel ? $historiqueActuel . "\n" . $ancienneVersionJson : $ancienneVersionJson;

    $contrat->setType($type);
    $contrat->setDateDebut($dateDebut);
    $contrat->setDateFin($dateFin);
    $contrat->setSalaire($salaire);
    $contrat->setAvantagesSociaux($avantagesSociaux);
    $contrat->setEmploye($employe);
    $contrat->setHistorique($nouvelHistorique);

    $entityManager->flush();

    return $this->redirect('/contrat');
}


    // public function supprimerContrat(int $id, EntityManagerInterface $entityManager, Request $request)
    // {
    //     $contrat = $entityManager->getRepository(Contrat::class)->find($id);
    //     $entityManager->remove($contrat);
    //     $entityManager->flush();
    //     return $this->redirect('/contrat');
    // }
    public function supprimerContrat(int $id, EntityManagerInterface $entityManager, Request $request)
    {
        $contrat = $entityManager->getRepository(Contrat::class)->find($id);

        if (!$contrat) {
            throw $this->createNotFoundException('No contrat found for id '.$id);
        }

        if ($this->isCsrfTokenValid('delete_contrat' . $contrat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contrat);
            $entityManager->flush();

            $this->addFlash('success', 'Contrat supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        $contrats = $entityManager->getRepository(Contrat::class)->findAll();
        return $this->render('contrat.html.twig', ['contrats' => $contrats]);
    }


    public function candidature(CandidatureRepository $candidatureRepository)
    {       
        $candidature=$candidatureRepository->findAll();
        return $this->render('candidature.html.twig', ["candidature"=>$candidature]) ;
    }
    public function ajoutCandidature(CandidatureRepository $candidatureRepository): Response
    {
        $candidature = $candidatureRepository->findAll();

        return $this->render('ajoutCandidature.html.twig', ['candidature' => $candidature]);
    }
    // public function ajouterCandidature(EntityManagerInterface $entityManager, Request $request)
    // {       
    
    // $poste = $request->request->get("poste");
    // $candidat = $request->request->get("candidatNom");
    // $email = $request->request->get("email");
    // $statut = $request->request->get("statut");

    // $errors = [];

    // if (empty($poste)) {
    //     $errors[] = "Le poste est requis.";
    // }
    // if (empty($email)) {
    //     $errors[] = "Le mail est requis.";
    // }
    // if (empty($candidat)) {
    //     $errors[] = "Le candidat est requis.";
    // }
    // if (empty($statut)) {
    //     $errors[] = "La statut est requiss.";
    // }

    // if (!empty($errors)) {
    //     return $this->render('erreurCandidature.html.twig', [
    //         'errors' => $errors,
    //     ]);
    // }

    // $candidature = new Candidature();
    // $candidature->setPoste($poste);
    // $candidature->setCandidatNom($candidat);
    // $candidature->setEmail($email);
    // $candidature->setStatut($statut);

    // $entityManager->persist($candidature);
    // $entityManager->flush();

    // return $this->render('ajouterCandidature.html.twig');
    // }

    public function ajouterCandidature(EntityManagerInterface $entityManager, Request $request)
{
    $poste = $request->request->get("poste");
    $candidat = $request->request->get("candidatNom");
    $email = $request->request->get("email");
    $statut = $request->request->get("statut");

    $errors = [];
    if (empty($poste)) $errors[] = "Le poste est requis.";
    if (empty($email)) $errors[] = "Le mail est requis.";
    if (empty($candidat)) $errors[] = "Le candidat est requis.";
    if (empty($statut)) $errors[] = "Le statut est requis.";

    // Gestion du CV (obligatoire)
    $cvFile = $request->files->get('cv');
    if (!$cvFile) $errors[] = "Le CV est requis.";
    elseif ($cvFile->getClientOriginalExtension() !== 'pdf') $errors[] = "Le CV doit être un PDF.";

    // Gestion de la lettre de motivation (facultatif)
    $lettreFile = $request->files->get('lettre_motivation');
    if ($lettreFile && $lettreFile->getClientOriginalExtension() !== 'pdf') {
        $errors[] = "La lettre de motivation doit être un PDF.";
    }

    if (!empty($errors)) {
        return $this->render('erreurCandidature.html.twig', [
            'errors' => $errors,
        ]);
    }

    // Sauvegarde des fichiers
    $cvFilename = uniqid() . '.' . $cvFile->guessExtension();
    $cvFile->move($this->getParameter('kernel.project_dir').'/public/uploads', $cvFilename);

    $lettreFilename = null;
    if ($lettreFile) {
        $lettreFilename = uniqid() . '.' . $lettreFile->guessExtension();
        $lettreFile->move($this->getParameter('kernel.project_dir').'/public/uploads', $lettreFilename);
    }

    $candidature = new Candidature();
    $candidature->setPoste($poste);
    $candidature->setCandidatNom($candidat);
    $candidature->setEmail($email);
    $candidature->setStatut($statut);
    $candidature->setCv($cvFilename);
    $candidature->setLettreMotivation($lettreFilename);
    $candidature->setDateCreation(new \DateTime());


    $entityManager->persist($candidature);
    $entityManager->flush();

    return $this->render('ajouterCandidature.html.twig');
}


    public function modifierCandidature(int $id, EntityManagerInterface $entityManager, Request $request)
    {
        $candidature = $entityManager->getRepository(Candidature::class)->find($id);
        return $this->render('modifierCandidature.html.twig', ['candidature' => $candidature]);
    }

//     public function modificationCandidature(EntityManagerInterface $entityManager, Request $request)
//     {
//     $id = $request->request->get("id");
//     $candidature = $entityManager->getRepository(Candidature::class)->find($id);


//     $poste = $request->request->get("poste");
//     $candidat = $request->request->get("candidatNom");
//     $email = $request->request->get("email");
//     $statut = $request->request->get("statut");

//     $errors = [];

//     if (empty($poste)) {
//         $errors[] = "Le poste est requis.";
//     }
//     if (empty($email)) {
//         $errors[] = "Le mail est requis.";
//     }
//     if (empty($candidat)) {
//         $errors[] = "Le candidat est requis.";
//     }
//     if (empty($statut)) {
//         $errors[] = "La statut est requiss.";
//     }

//     if (!empty($errors)) {
//         return $this->render('erreurCandidature.html.twig', [
//             'errors' => $errors,
//         ]);
//     }

//     $candidature->setPoste($poste);
//     $candidature->setCandidatNom($candidat);
//     $candidature->setEmail($email);
//     $candidature->setStatut($statut);

//     $entityManager->flush();

//     return $this->redirect('/candidature');
// }

    public function modificationCandidature(EntityManagerInterface $entityManager, Request $request)
    {
    $id = $request->request->get("id");
    $candidature = $entityManager->getRepository(Candidature::class)->find($id);

    if (!$candidature) {
        throw $this->createNotFoundException('Candidature non trouvée');
    }

    $poste = $request->request->get("poste");
    $candidat = $request->request->get("candidatNom");
    $email = $request->request->get("email");
    $statut = $request->request->get("statut");
    $commentaire = $request->request->get("commentaire");

    $errors = [];
    if (empty($poste)) $errors[] = "Le poste est requis.";
    if (empty($email)) $errors[] = "Le mail est requis.";
    if (empty($candidat)) $errors[] = "Le candidat est requis.";
    if (empty($statut)) $errors[] = "Le statut est requis.";

    // Gestion des fichiers uploadés (facultatif)
    $cvFile = $request->files->get('cv');
    $lettreFile = $request->files->get('lettre_motivation');

    if ($cvFile && $cvFile->getClientOriginalExtension() !== 'pdf') {
        $errors[] = "Le CV doit être un fichier PDF.";
    }
    if ($lettreFile && $lettreFile->getClientOriginalExtension() !== 'pdf') {
        $errors[] = "La lettre de motivation doit être un fichier PDF.";
    }

    if (!empty($errors)) {
        return $this->render('erreurCandidature.html.twig', [
            'errors' => $errors,
        ]);
    }

    // Mise à jour des données
    $candidature->setPoste($poste);
    $candidature->setCandidatNom($candidat);
    $candidature->setEmail($email);
    $candidature->setStatut($statut);
    $candidature->setCommentaire($commentaire);

    // Dossier d'upload
    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

    // Mise à jour du CV si nouveau fichier uploadé
    if ($cvFile) {
        // Supprimer l'ancien fichier si existant
        if ($candidature->getCv() && file_exists($uploadDir . '/' . $candidature->getCv())) {
            unlink($uploadDir . '/' . $candidature->getCv());
        }
        $cvFilename = uniqid() . '.' . $cvFile->guessExtension();
        $cvFile->move($uploadDir, $cvFilename);
        $candidature->setCv($cvFilename);
    }

    // Mise à jour de la lettre de motivation si nouveau fichier uploadé
    if ($lettreFile) {
        if ($candidature->getLettreMotivation() && file_exists($uploadDir . '/' . $candidature->getLettreMotivation())) {
            unlink($uploadDir . '/' . $candidature->getLettreMotivation());
        }
        $lettreFilename = uniqid() . '.' . $lettreFile->guessExtension();
        $lettreFile->move($uploadDir, $lettreFilename);
        $candidature->setLettreMotivation($lettreFilename);
    }

    $entityManager->flush();

    return $this->redirect('/candidature');
}


    // public function supprimerCandidature(int $id, EntityManagerInterface $entityManager, Request $request)
    // {
    //     $candidature = $entityManager->getRepository(Candidature::class)->find($id);
    //     $entityManager->remove($candidature);
    //     $entityManager->flush();
    //     return $this->redirect('/candidature');
    // }
    public function supprimerCandidature(int $id, EntityManagerInterface $entityManager, Request $request)
    {
        $candidature = $entityManager->getRepository(Candidature::class)->find($id);
         if ($this->isCsrfTokenValid('delete_candidature' . $candidature->getId(), $request->request->get('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();

             $this->addFlash('success', 'Candidature supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        $candidature = $entityManager->getRepository(Candidature::class)->findAll();
        return $this->render('candidature.html.twig', ['candidature' => $candidature]);
    }

    public function timesheet(TimesheetRepository $timesheetRepository)
    {       
        $timesheet=$timesheetRepository->findAll();
        return $this->render('timesheet.html.twig', ["timesheet"=>$timesheet]) ;
    }
    public function ajoutTimesheet(EmployeRepository $employeRepository): Response
        {
            $employes = $employeRepository->findAll();

            return $this->render('ajoutTimesheet.html.twig', ['employes' => $employes]);
        }

    public function ajouterTimesheet(EntityManagerInterface $entityManager, Request $request, EmployeRepository $employeRepository)
    {       

    $date = $request->request->get("date");
    $heuresTravail = $request->request->get("heuresTravail");
    $heuresSup = $request->request->get("heuresSup");
    $motif = $request->request->get("motif");

    $employeId = $request->request->get("employe");

    $employe = $employeRepository->find($employeId);

    $errors = [];

    if (empty($heuresSup)) {
        $errors[] = "Les heures sup sont requises.";
    }
    if (empty($date)) {
        $errors[] = "La date est requise.";
    }
    if (empty($heuresTravail)) {
        $errors[] = "Les heures de travailles sont requises.";
    }

    if (!empty($errors)) {
        return $this->render('erreurTimesheet.html.twig', [
            'errors' => $errors,
        ]);
    }

    $timesheet = new Timesheet();
    $timesheet->setDate($date);
    $timesheet->setHeuresTravail($heuresTravail);
    $timesheet->setHeuresSup($heuresSup);
    $timesheet->setEmploye($employe);
    $timesheet->setMotif($motif);

    $entityManager->persist($timesheet);
    $entityManager->flush();

    return $this->render('ajouterTimesheet.html.twig');
    }

    public function modifierTimesheet(int $id, EntityManagerInterface $entityManager, Request $request, EmployeRepository $employeRepository)
    {
        $timesheet = $entityManager->getRepository(Timesheet::class)->find($id);
        $employes = $employeRepository->findAll(); // Récupérez la liste des employés

        return $this->render('modifierTimesheet.html.twig', [
            'timesheet' => $timesheet,
            'employes' => $employes, // Passez la liste des employés au template
        ]);
    }

    public function modificationTimesheet(EntityManagerInterface $entityManager, Request $request, TimesheetRepository $timesheetRepository, EmployeRepository $employeRepository)
    {
    $id = $request->request->get("id");
    $timesheet = $entityManager->getRepository(Timesheet::class)->find($id);

    // Récupération de la liste des employés
    $employes = $employeRepository->findAll();

    $date = $request->request->get("date");
    $heuresTravail = $request->request->get("heuresTravail");
    $heuresSup = $request->request->get("heuresSup");
    $motif = $request->request->get("motif");

    $employeId = $request->request->get("employe");

    $errors = [];

    if (empty($heuresSup)) {
        $errors[] = "Les heures sup sont requises.";
    }
    if (empty($date)) {
        $errors[] = "La date est requise.";
    }
    if (empty($heuresTravail)) {
        $errors[] = "Les heures de travailles sont requises.";
    }

    if (!empty($errors)) {
        return $this->render('erreurTimesheet.html.twig', [
            'errors' => $errors,
        ]);
    }

    // Recherche de l'employé sélectionné
    $employe = $employeRepository->find($employeId);

    $timesheet->setDate($date);
    $timesheet->setHeuresTravail($heuresTravail);
    $timesheet->setHeuresSup($heuresSup);
    $timesheet->setEmploye($employe);
    $timesheet->setMotif($motif);

    $entityManager->flush();

    return $this->redirect('/timesheet');
    }

    // public function supprimerTimesheet(int $id, EntityManagerInterface $entityManager, Request $request)
    // {
    //     $timesheet = $entityManager->getRepository(Timesheet::class)->find($id);
    //     $entityManager->remove($timesheet);
    //     $entityManager->flush();
    //     return $this->redirect('/timesheet');
    // }
    public function supprimerTimesheet(int $id, EntityManagerInterface $entityManager, Request $request)
    {
        $timesheet = $entityManager->getRepository(Timesheet::class)->find($id);
         if ($this->isCsrfTokenValid('delete_timesheet' . $timesheet->getId(), $request->request->get('_token'))) {
            $entityManager->remove($timesheet);
            $entityManager->flush();

             $this->addFlash('success', 'Timesheet supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        $timesheet = $entityManager->getRepository(Timesheet::class)->findAll();
        return $this->render('timesheet.html.twig', ['timesheet' => $timesheet]);
    }

    public function demandeConge(DemandeCongeRepository $demandeCongeRepository)
    {       
        $demandeConge=$demandeCongeRepository->findAll();
        return $this->render('demandeConge.html.twig', ["demandeConge"=>$demandeConge]) ;
    }

    public function ajoutDemandeConge(EmployeRepository $employeRepository): Response
        {
            $employes = $employeRepository->findAll();

            return $this->render('ajoutDemandeConge.html.twig', ['employes' => $employes]);
        }

    public function ajouterDemandeConge(EntityManagerInterface $entityManager, Request $request, EmployeRepository $employeRepository)
    {       

    $typeConge = $request->request->get("typeConge");
    $dateDebut = $request->request->get("dateDebut");
    $dateFin = $request->request->get("dateFin");
    $statut = $request->request->get("statut");

    $employeId = $request->request->get("employe");

    $employe = $employeRepository->find($employeId);

    $errors = [];

    if (empty($typeConge)) {
        $errors[] = "Le type de conges est requis.";
    }
    if (empty($dateDebut)) {
        $errors[] = "La date de début est requise.";
    }
    if (empty($dateFin)) {
        $errors[] = "La date de fin est requise.";
    }
    if (empty($statut)) {
        $errors[] = "La statut est requise.";
    }


    if (!empty($errors)) {
        return $this->render('erreurDemandeConge.html.twig', [
            'errors' => $errors,
        ]);
    }

    $demandeConge = new DemandeConge();
    $demandeConge->setTypeConge($typeConge);
    $demandeConge->setDateDebut($dateDebut);
    $demandeConge->setDateFin($dateFin);
    $demandeConge->setStatut($statut);
    $demandeConge->setEmploye($employe);

    $entityManager->persist($demandeConge);
    $entityManager->flush();

    return $this->render('ajouterDemandeConge.html.twig');
    }

    public function modifierDemandeConge(int $id, EntityManagerInterface $entityManager, Request $request, EmployeRepository $employeRepository)
    {
        $demandeConge = $entityManager->getRepository(DemandeConge::class)->find($id);
        $employes = $employeRepository->findAll(); // Récupérez la liste des employés

        return $this->render('modifierDemandeConge.html.twig', [
            'demandeConge' => $demandeConge,
            'employes' => $employes, // Passez la liste des employés au template
        ]);
    }
    public function modificationDemandeConge(EntityManagerInterface $entityManager, Request $request, DemandeCongeRepository $demandeCongeRepository, EmployeRepository $employeRepository)
    {

    $id = $request->request->get("id");
    $demandeConge = $entityManager->getRepository(DemandeConge::class)->find($id);

    // Récupération de la liste des employés
    $employes = $employeRepository->findAll();

    $typeConge = $request->request->get("typeConge");
    $dateDebut = $request->request->get("dateDebut");
    $dateFin = $request->request->get("dateFin");
    $statut = $request->request->get("statut");
    $motif = $request->request->get("motif");
    $commentaire = $request->request->get("commentaire");

    $employeId = $request->request->get("employe");


    $errors = [];

    if (empty($typeConge)) {
        $errors[] = "Le type de conges est requis.";
    }
    if (empty($dateDebut)) {
        $errors[] = "La date de début est requise.";
    }
    if (empty($dateFin)) {
        $errors[] = "La date de fin est requise.";
    }
    if (empty($statut)) {
        $errors[] = "La statut est requise.";
    }


    if (!empty($errors)) {
        return $this->render('erreurDemandeConge.html.twig', [
            'errors' => $errors,
        ]);
    }

    // Recherche de l'employé sélectionné
    $employe = $employeRepository->find($employeId);

    $demandeConge->setTypeConge($typeConge);
    $demandeConge->setDateDebut($dateDebut);
    $demandeConge->setDateFin($dateFin);
    $demandeConge->setStatut($statut);
    $demandeConge->setMotif($motif);
    $demandeConge->setCommentaire($commentaire);
    $demandeConge->setEmploye($employe);


    $entityManager->flush();

    return $this->redirect('/demandeConge');
    }

    // public function supprimerDemandeConge(int $id, EntityManagerInterface $entityManager, Request $request)
    // {
    // $demandeConge = $entityManager->getRepository(DemandeConge::class)->find($id);
    // $entityManager->remove($demandeConge);
    // $entityManager->flush();
    // return $this->redirect('/demandeConge');
    // }
    public function supprimerDemandeConge(int $id, EntityManagerInterface $entityManager, Request $request)
    {
        $demandeConge = $entityManager->getRepository(DemandeConge::class)->find($id);
         if ($this->isCsrfTokenValid('delete_demande' . $demandeConge->getId(), $request->request->get('_token'))) {
            $entityManager->remove($demandeConge);
            $entityManager->flush();

             $this->addFlash('success', 'Demande Congé supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        $demandeConge = $entityManager->getRepository(DemandeConge::class)->findAll();
        return $this->render('demandeConge.html.twig', ['demandeConge' => $demandeConge]);
    }

    public function home()
    {
        return $this->render("home.html.twig");
    }

    // public function dashboard(
    //     EmployeRepository $employeRepository,
    //     ContratRepository $contratRepository,
    //     DemandeCongeRepository $demandeCongeRepository,
    //     TimesheetRepository $timesheetRepository,
    //     CandidatureRepository $candidatureRepository
    // ) {
    //     // Employés
    //     $totalEmployes = $employeRepository->count([]);
    //     // Évolution du nombre d'employés (par mois)
    //     $employesParMois = $employeRepository->getEvolutionParMois(); // À créer (voir plus bas)
    
    //     // Contrats
    //     $contratsActifs = $contratRepository->countActifs(); // À créer
    //     $contratsExpiration3Mois = $contratRepository->countExpirationProche(); // À créer
    //     $salairesDistribution = $contratRepository->getDistributionSalaires(); // À créer
    
    //     // Congés
    //     $congesEnAttente = $demandeCongeRepository->count(['statut' => 'En attente']);
    //     $congesApprouvesMois = $demandeCongeRepository->countApprouvesCeMois(); // À créer
    //     $typesConges = $demandeCongeRepository->getTypesPlusDemandes(); // À créer
    //     $calendrierConges = $demandeCongeRepository->getCalendrierConges(); // À créer
    
    //     // Timesheets
    //     $timesheetsMois = $timesheetRepository->countCeMois(); // À créer
    //     $heuresTravailTotales = $timesheetRepository->getHeuresTravailTotales(); // À créer
    //     $heuresSupTotales = $timesheetRepository->getHeuresSupTotales(); // À créer
    //     $heuresParEmploye = $timesheetRepository->getHeuresParEmploye(); // À créer
    
    //     // Candidatures
    //     $totalCandidatures = $candidatureRepository->count([]);
    //     $nouvellesCandidaturesMois = $candidatureRepository->countCeMois(); // À créer
    //     $candidaturesParPoste = $candidatureRepository->getRepartitionParPoste(); // À créer
    
    //     // Préparer les données pour Charts.css (ratios entre 0 et 1 pour les graphiques)
    //     // ... voir section suivante
    
    //     // Passer les données au template
    //     return $this->render('dashboard.html.twig', [
    //         'totalEmployes' => $totalEmployes,
    //         'evolutionEmployes' => $employesParMois,
    //         'contratsActifs' => $contratsActifs,
    //         'contratsExpiration3Mois' => $contratsExpiration3Mois,
    //         'salairesDistribution' => $salairesDistribution,
    //         'congesEnAttente' => $congesEnAttente,
    //         'congesApprouvesMois' => $congesApprouvesMois,
    //         'typesConges' => $typesConges,
    //         'calendrierConges' => $calendrierConges,
    //         'timesheetsMois' => $timesheetsMois,
    //         'heuresTravailTotales' => $heuresTravailTotales,
    //         'heuresSupTotales' => $heuresSupTotales,
    //         'heuresParEmploye' => $heuresParEmploye,
    //         'totalCandidatures' => $totalCandidatures,
    //         'nouvellesCandidaturesMois' => $nouvellesCandidaturesMois,
    //         'candidaturesParPoste' => $candidaturesParPoste,
    //     ]);
    // }
    public function dashboard(
        EmployeRepository $employeRepository,
        ContratRepository $contratRepository,
        DemandeCongeRepository $demandeCongeRepository,
        TimesheetRepository $timesheetRepository,
        CandidatureRepository $candidatureRepository
    ): Response {
        // --- Employés ---
        $totalEmployes = $employeRepository->count([]);

        // Evolution du nombre d'employés par mois
        $evolutionRaw = $employeRepository->getEvolutionParMois();
        $maxEmp = max(array_column($evolutionRaw, 'total')) ?: 1;
        $evolutionEmployes = [];
        foreach ($evolutionRaw as $i => $row) {
            $start = $i === 0 ? 0 : $evolutionRaw[$i - 1]['total'] / $maxEmp;
            $end = $row['total'] / $maxEmp;
            $evolutionEmployes[] = [
                'value' => $row['total'],
                'start' => $start,
                'end' => $end,
            ];
        }

        // --- Contrats ---
        $contratsActifs = $contratRepository->countActifs();
        $contratsExpiration3Mois = $contratRepository->countExpirationProche();
        $salairesRaw = $contratRepository->getDistributionSalaires();
        $maxSalaireCount = max(array_column($salairesRaw, 'count')) ?: 1;
        $salairesDistribution = array_map(function ($item) use ($maxSalaireCount) {
            $item['ratio'] = $item['count'] / $maxSalaireCount;
            return $item;
        }, $salairesRaw);

        // --- Congés ---
        $congesEnAttente = $demandeCongeRepository->count(['statut' => 'En attente']);
        $congesApprouvesMois = $demandeCongeRepository->countApprouvesCeMois();
        $typesCongesRaw = $demandeCongeRepository->getTypesPlusDemandes();
        $maxTypeCount = max(array_column($typesCongesRaw, 'count')) ?: 1;
        $typesConges = array_map(function ($item) use ($maxTypeCount) {
            $item['ratio'] = $item['count'] / $maxTypeCount;
            return $item;
        }, $typesCongesRaw);
        $calendrierConges = $demandeCongeRepository->getCalendrierConges();

        // --- Timesheets ---
        $timesheetsMois = $timesheetRepository->countCeMois();
        $heuresTravailTotales = $timesheetRepository->getHeuresTravailTotales();
        $heuresSupTotales = $timesheetRepository->getHeuresSupTotales();
        $heuresParEmployeRaw = $timesheetRepository->getHeuresParEmploye();
        $maxHeures = max(array_column($heuresParEmployeRaw, 'heures')) ?: 1;
        $heuresParEmploye = array_map(function ($item) use ($maxHeures) {
            $item['ratio'] = $item['heures'] / $maxHeures;
            return $item;
        }, $heuresParEmployeRaw);

        // --- Candidatures ---
        $totalCandidatures = $candidatureRepository->count([]);
        $nouvellesCandidaturesMois = $candidatureRepository->countCeMois();
        $candidaturesParPosteRaw = $candidatureRepository->getRepartitionParPoste();
        $maxCandidatures = max(array_column($candidaturesParPosteRaw, 'count')) ?: 1;
        $candidaturesParPoste = array_map(function ($item) use ($maxCandidatures) {
            $item['ratio'] = $item['count'] / $maxCandidatures;
            return $item;
        }, $candidaturesParPosteRaw);

        $calendrierConges = $demandeCongeRepository->getCalendrierConges();
        // Transforme pour JS (array d'événements)
        $events = [];
        foreach ($calendrierConges as $conge) {
            $events[] = [
                'title' => $conge['employe'] . ' - ' . $conge['type'],
                'start' => $conge['dateDebut'],
                'end' => (new \DateTime($conge['dateFin']))->modify('+1 day')->format('Y-m-d'), // FullCalendar exclut la date de fin, donc +1j
                'color' => '#3fc0c7', // Optionnel: couleur
            ];
        }

        return $this->render('dashboard.html.twig', [
            'calendrierConges' => $calendrierConges,
            'congesEventsJson' => json_encode($events),
            'totalEmployes' => $totalEmployes,
            'evolutionEmployes' => $evolutionEmployes,
            'contratsActifs' => $contratsActifs,
            'contratsExpiration3Mois' => $contratsExpiration3Mois,
            'salairesDistribution' => $salairesDistribution,
            'congesEnAttente' => $congesEnAttente,
            'congesApprouvesMois' => $congesApprouvesMois,
            'typesConges' => $typesConges,
            'calendrierConges' => $calendrierConges,
            'timesheetsMois' => $timesheetsMois,
            'heuresTravailTotales' => $heuresTravailTotales,
            'heuresSupTotales' => $heuresSupTotales,
            'heuresParEmploye' => $heuresParEmploye,
            'totalCandidatures' => $totalCandidatures,
            'nouvellesCandidaturesMois' => $nouvellesCandidaturesMois,
            'candidaturesParPoste' => $candidaturesParPoste,
        ]);
    }
}