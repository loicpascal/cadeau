<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamRejoinType;
use App\Form\TeamType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TeamController extends Controller
{
    /**
     * @Route("/team", name="team_list")
     */
    public function listAction()
    {
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findAllMyTeams($this->getUser()->getID());

        return $this->render('team/list.html.twig', [
            'teams' => $teams,
        ]);
    }

    /**
     * @Route("/team/new", name="team_new")
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $team = new Team();

        $breadcrumb = [$this->generateUrl('team_list') => "Mes groupes", "" => "Nouveau groupe"];

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $team = $form->getData();

            $team->setLeader($this->getUser());
            // Random code creation
            $team->setCode($this->generateRandomString());
            $team->addUser($this->getUser());

            $em->persist($team);
            $em->flush();

            $this->sendEmailInsert($team);

            $this->addFlash('success', 'Le groupe ' . $team->getName() . ' a bien été créé. Transmettez le code ' . $team->getCode() . ' aux personnes qui souhaitent le rejoindre.');

            return $this->redirectToRoute('team_list');
        }
        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
            'breadcrumb' => $breadcrumb
        ]);
    }

    /**
     * @Route("/team/{id}/update", name="team_update", requirements={"id"="\d+"})
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Aucune idée trouvée pour l\'identifiant : ' . $id);
        }
        if (!$this->checkAccess($team)) {
            return $this->redirectToRoute('team_list');
        }

        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // Envoi d'un mail de confirmation
            $this->sendEmailUpdate($team);

            $this->addFlash('success', 'Les modifications ont bien été prises en compte');
            return $this->redirectToRoute('team_list');
        }

        return $this->render('team/update.html.twig', [
            'form' => $form->createView(),
            'breadcrumb' => [$this->generateUrl('team_list') => "Mes groupes", "" => $team->getName()],
            'team' => $team
        ]);
    }

    /**
     * @Route("/team/{id}/delete", name="team_delete", requirements={"id"="\d+"})
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Aucun groupe trouvé pour l\'identifiant : ' . $id);
        }
        if (!$this->checkAccess($team)) {
            return $this->redirectToRoute('team_list');
        }

        $em->remove($team);
        $em->flush();

        $this->addFlash('success', 'Le groupe a bien été supprimé');

        return $this->redirectToRoute('team_list');
    }

    /**
     * @Route("/team/rejoin", name="team_rejoin")
     */
    public function rejoinAction(SessionInterface $session, Request $request)
    {
        $breadcrumb = [$this->generateUrl('team_list') => "Mes groupes", "" => "Rejoindre un groupe"];

        $form = $this->createForm(TeamRejoinType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $team = $em->getRepository(Team::class)->findOneBy(['code' => $form->getData()->getCode()]);

            $team->addUser($this->getUser());
            $em->flush();

            $this->addFlash('success', 'Vous avez rejoint le groupe ' . $team->getName() . ' !');

            $session->set('team', $team);

            return $this->redirectToRoute('team_list');
        }
        return $this->render('team/rejoin.html.twig', [
            'form' => $form->createView(),
            'breadcrumb' => $breadcrumb
        ]);
    }

    /**
     * @Route("/team/{id}/leave", name="team_leave")
     */
    public function leaveAction(SessionInterface $session, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Aucun groupe trouvé pour l\'identifiant : ' . $id);
        }
        if (!$this->checkAccessLeave($team)) {
            return $this->redirectToRoute('team_list');
        }

        $team->removeUser($this->getUser());
        $em->flush();

        $this->addFlash('success', 'Vous avez bien quitté le groupe');

        $session->remove('team');

        return $this->redirectToRoute('team_list');
    }

    /**
     * @Route("/team/{id}/connect", name="team_connect")
     */
    public function connectAction(SessionInterface $session, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($id);
        $session->set('team', $team);

        return $this->redirectToRoute('idee_list');
    }

    /**
     * @Route("/team/disconnect", name="team_disconnect")
     */
    public function disconnectAction(SessionInterface $session)
    {
        $session->remove('team');

        return $this->redirectToRoute('team_list');
    }

    private function checkAccess(Team $team)
    {
        return $this->getUser() === $team->getLeader();
    }

    private function checkAccessLeave(Team $team)
    {
        return $this->getUser() !== $team->getLeader();
    }

    private function sendEmailInsert(Team $team)
    {
        if ($team->getLeader()->getId() == 1) {
            // Si c'est l'utilisateur ADMIN
            return;
        }
        $body = $team->getLeader()->getFirstname() . " a ajouté l'idée " . $team->getName() . " dans votre application web !";
        $message = (new \Swift_Message('Ajout d\'un groupe'))
            ->setFrom('nepasrepondre@loic-pascal.fr')
            ->setTo('loic.pascal@gmail.com')
            ->setBody(
                $body,
                'text/html'
            );
        $this->get('mailer')->send($message);
    }

    private function sendEmailUpdate(Team $team)
    {
        $message = (new \Swift_Message('Modification d\'un groupe'))
            ->setFrom('nepasrepondre@loic-pascal.fr')
            ->setTo('loic.pascal@gmail.com')
            ->setBody(
                $team->getLeader()->getFirstname() . " a mis à jour le groupe . \"" . $team->getName() . "\" dans votre application web !",
                'text/html'
            );
        $this->get('mailer')->send($message);
    }

    private function generateRandomString($length = 6) {
        do {
            $characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $code = '';
            for ($i = 0; $i < $length - 1; $i++) {
                $code .= $characters[rand(0, $charactersLength - 1)];
            }
            $code .= mt_rand(1, 9);
        }
        while ($this->getDoctrine()->getRepository(Team::class)->countByCode($code) > 0);

        return $code;
    }
}
