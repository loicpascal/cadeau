<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Idee;
use App\Form\CommentType;
use App\Form\IdeeType;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IdeeController extends Controller
{
    /**
     * @Route("/", name="idee_list")
     */
    public function listAction()
    {
        $idees = $this->getDoctrine()
            ->getRepository(Idee::class)
            ->findBy(
                [
                    'user' => $this->getUser(),
                    'user_adding' => null,
                    'archived' => false
                ],
                ['id' => 'DESC']
            );

        return $this->render('idee/list.html.twig', [
            'idees' => $idees,
        ]);
    }

    /**
     * @Route("/idee/archived", name="idee_archived_list")
     */
    public function listArchivedAction()
    {
        $idees = $this->getDoctrine()
            ->getRepository(Idee::class)
            ->findBy(
                [
                    'user' => $this->getUser(),
                    'user_adding' => null,
                    'archived' => true
                ],
		['id' => 'DESC']
            );

        return $this->render('idee/list.html.twig', [
            'idees' => $idees,
            'archived' => true,
        ]);
    }

    /**
     * @Route("/idee/{id}/show", name="idee_show", requirements={"id"="\d+"})
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $idee = $em->getRepository(Idee::class)->find($id);

        if (!$idee) {
            throw $this->createNotFoundException('Aucune idée trouvée pour l\'identifiant : ' . $id);
        }
        if (!$this->checkAccessShow($idee)) {
            return $this->redirectToRoute('idee_list');
        }

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('comment_new', ['id_idee' => $id])
        ]);

        $user = $idee->getUser();
        return $this->render('idee/show.html.twig', [
            'idee' => $idee,
            'breadcrumb' => [
                $this->generateUrl('user_list') => "Membres",
                $this->generateUrl('user_show', ['id' => $user->getId()]) => $user->getFirstname(),
                "" => $idee->getLibelle()
            ],
            'formComment' => $formComment->createView()
        ]);
    }

    /**
     * @Route("/idee/new/{id_user}", name="idee_new")
     */
    public function newAction(Request $request, $id_user = null) {
        $em = $this->getDoctrine()->getManager();
        $idee = new Idee();

        if (is_null($id_user)) {
            $user = $this->getUser();
            $breadcrumb = [$this->generateUrl('idee_list') => "Mes idées", "" => "Nouvelle idée"];
        } else {
            $user = $this->getDoctrine()->getRepository(User::class)->find($id_user);
            $breadcrumb = [$this->generateUrl('user_list') => "Membres", "" => "Nouvelle idée pour " . $user->getFirstname()];
        }

        $form = $this->createForm(IdeeType::class, $idee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $idee = $form->getData();

            // Ajoute une idée pour un autre
            if (!is_null($id_user)) {
                $idee->setUserAdding($this->getUser());
            }

            $idee->setState(0);
            $idee->setArchived(0);
            $idee->setUser($user);
            $em->persist($idee);
            $em->flush();

            $this->sendEmailInsert($idee);

            $this->addFlash('success', 'L\'idée a bien été ajoutée à votre liste');

            if (is_null($id_user)) {
                // Si l'idée est pour le membre connecté
                return $this->redirectToRoute('idee_list');
            } else {
                // Si l'idée est pour un autre membre
                return $this->redirectToRoute('user_show', ['id' => $id_user]);
            }
        }
        return $this->render('idee/new.html.twig', [
            'form' => $form->createView(),
            'breadcrumb' => $breadcrumb,
            'user' => $user
        ]);
    }

    /**
     * @Route("/idee/{id}/update", name="idee_update", requirements={"id"="\d+"})
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $idee = $em->getRepository(Idee::class)->find($id);

        if (!$idee) {
            throw $this->createNotFoundException('Aucune idée trouvée pour l\'identifiant : ' . $id);
        }
        if (!$this->checkAccessUpdate($idee)) {
            return $this->redirectToRoute('idee_list');
        }

        $form = $this->createForm(IdeeType::class, $idee);

        if ($idee->getComments()) {
            $comment = new Comment();
            $formComment = $this->createForm(CommentType::class, $comment, [
                'action' => $this->generateUrl('comment_new', ['id_idee' => $id])
            ]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // Envoi d'un mail de confirmation
            $this->sendEmailUpdate($idee);

            $this->addFlash('success', 'Les modifications ont bien été prises en compte');
            return $this->redirectToRoute('idee_list');
        }

        return $this->render('idee/update.html.twig', [
            'form' => $form->createView(),
            'formComment' => $formComment->createView(),
            'breadcrumb' => [$this->generateUrl('idee_list') => "Mes idées", "" => $idee->getLibelle()],
            'idee' => $idee
        ]);
    }

    /**
     * @Route("/idee/{id}/archive", name="idee_archive", requirements={"id"="\d+"})
     */
    public function archiveAction($id) {
        $em = $this->getDoctrine()->getManager();
        $idee = $em->getRepository(Idee::class)->find($id);

        if (!$idee) {
            throw $this->createNotFoundException('Aucune idée trouvée pour l\'identifiant : ' . $id);
        }

        $idee->setArchived(true);
        $em->persist($idee);
        $em->flush();

        return $this->redirectToRoute('idee_archived_list');
    }

    /**
     * @Route("/idee/{id}/unarchive", name="idee_unarchive", requirements={"id"="\d+"})
     */
    public function unarchiveAction($id) {
        $em = $this->getDoctrine()->getManager();
        $idee = $em->getRepository(Idee::class)->find($id);

        if (!$idee) {
            throw $this->createNotFoundException('Aucune idée trouvée pour l\'identifiant : ' . $id);
        }

        $idee->setArchived(false);
        $em->persist($idee);
        $em->flush();

        return $this->redirectToRoute('idee_list');
    }

    /**
     * @Route("/idee/{id}/{user_id}/state_update_to_take", name="idee_state_update_to_take", requirements={"id"="\d+"})
     */
    public function stateUpdateToTakeAction($id, $user_id) {
        $em = $this->getDoctrine()->getManager();
        $idee = $em->getRepository(Idee::class)->find($id);

        if (!$idee) {
            throw $this->createNotFoundException('Aucune idée trouvée pour l\'identifiant : ' . $id);
        }

        $idee->setState(1);
        $idee->setUserTaking($this->getUser());
        $em->persist($idee);
        $em->flush();

        return $this->redirectToRoute('user_show', ['id' => $user_id]);
    }

    /**
     * @Route("/idee/{id}/{user_id}/state_cancel_to_take", name="idee_state_cancel_to_take", requirements={"id"="\d+"})
     */
    public function stateCancelToTakeAction($id, $user_id) {
        $em = $this->getDoctrine()->getManager();
        $idee = $em->getRepository(Idee::class)->find($id);

        if (!$idee) {
            throw $this->createNotFoundException('Aucune idée trouvée pour l\'identifiant : ' . $id);
        }

        $idee->setState(0);
        $idee->setUserTaking(null);
        $em->persist($idee);
        $em->flush();

        return $this->redirectToRoute('user_show', ['id' => $user_id]);
    }

    /**
     * @Route("/idee/{id}/{user_id}/state_update_taken", name="idee_state_update_taken", requirements={"id"="\d+"})
     */
    public function stateUpdateTakenAction($id, $user_id) {
        $em = $this->getDoctrine()->getManager();
        $idee = $em->getRepository(Idee::class)->find($id);

        if (!$idee) {
            throw $this->createNotFoundException('Aucune idée trouvée pour l\'identifiant : ' . $id);
        }

        $idee->setState(2);
        $idee->setUserTaking($this->getUser());
        $em->persist($idee);
        $em->flush();

        return $this->redirectToRoute('user_show', ['id' => $user_id]);
    }

    /**
     * @Route("/idee/{id}/delete", name="idee_delete", requirements={"id"="\d+"})
     */
    public function deleteAction($id) {
        $entityManager = $this->getDoctrine()->getManager();
        $idee = $entityManager->getRepository(Idee::class)->find($id);

        if (!$idee) {
            throw $this->createNotFoundException('Aucune idée trouvé pour l\'identifiant : ' . $id);
        }
        if (!$this->checkAccessDelete($idee)) {
            return $this->redirectToRoute('idee_list');
        }

        $entityManager->remove($idee);
        $entityManager->flush();

        $this->addFlash('success', 'L\'idée a bien été supprimée');

        if (is_null($idee->getUserAdding())) {
            // Si le membre à supprimé une de ses idées
            return $this->redirectToRoute('idee_list');
        } else {
            // Si le membre a supprimé l'idée d'un autre membre
            return $this->redirectToRoute('user_show', ['id' => $idee->getUser()->getId()]);
        }
    }

    private function checkAccessShow(Idee $idee) {
        return $this->getUser()->getId() != $idee->getUser()->getId();
    }

    private function checkAccessUpdate(Idee $idee) {
        return
            (is_null($idee->getUserAdding()) && $this->getUser()->getId() == $idee->getUser()->getId())
            ||
            (!is_null($idee->getUserAdding()) && $idee->getUserAdding()->getId() == $this->getUser()->getId());
    }

    private function checkAccessDelete(Idee $idee) {
        return
            (is_null($idee->getUserAdding()) && $this->getUser()->getId() == $idee->getUser()->getId())
            ||
            (!is_null($idee->getUserAdding()) && $idee->getUserAdding()->getId() == $this->getUser()->getId());
    }

    private function sendEmailInsert(Idee $idee) {
        if ($idee->getUser()->getId() == 1) {
            return;
        }
        if ($userAdding = $idee->getUserAdding()) {
            $body = $userAdding->getFirstname() . " a ajouté l'idée " . $idee->getLibelle() . " à " . $idee->getUser()->getFirstname() . " dans votre application web !";
        } else {
            $body = $idee->getUser()->getFirstname() . " a ajouté l'idée " . $idee->getLibelle() . " dans votre application web !";
        }
        $message = (new \Swift_Message('Ajout d\'une idée cadeau'))
            ->setFrom('nepasrepondre@loic-pascal.fr')
            ->setTo('loic.pascal@gmail.com')
            ->setBody(
                $body,
                'text/html'
            );
        $this->get('mailer')->send($message);
    }

    private function sendEmailUpdate(Idee $idee) {
        $message = (new \Swift_Message('Modification d\'une idée cadeau'))
            ->setFrom('nepasrepondre@loic-pascal.fr')
            ->setTo('loic.pascal@gmail.com')
            ->setBody(
                $idee->getUser()->getFirstname() . " a mis à jour l'idée . \"" . $idee->getLibelle() . "\" dans votre application web !",
                'text/html'
            );
        $this->get('mailer')->send($message);
    }
}
