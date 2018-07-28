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
                ['user' => $this->getUser()]
            );

        return $this->render('idee/list.html.twig', [
            'idees' => $idees,
        ]);
    }

    /**
     * @Route("/idee/{id}/show", name="idee_show", requirements={"id"="\d+"})
     */
    public function showAction($id) {
        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('comment_new', ['id_idee' => $id])
        ]);

        $em = $this->getDoctrine()->getManager();
        $idee = $em->getRepository(Idee::class)->find($id);

        if (!$idee) {
            throw $this->createNotFoundException('Aucune idée trouvée pour l\'identifiant : ' . $id);
        }

        return $this->render('idee/show.html.twig', [
            'idee' => $idee,
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
        } else {
            $user = $this->getDoctrine()->getRepository(User::class)->find($id_user);
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
            $idee->setUser($user);
            $em->persist($idee);
            $em->flush();

            if (is_null($id_user)) {
                return $this->redirectToRoute('idee_list');
            } else {
                return $this->redirectToRoute('user_show', ['id' => $id_user]);
            }
        }
        return $this->render('idee/new.html.twig', [
            'form' => $form->createView(),
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

        $form = $this->createForm(IdeeType::class, $idee);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('idee_list');
        }

        return $this->render('idee/update.html.twig', [
            'form' => $form->createView(),
            'idee' => $idee
        ]);
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
            throw $this->createNotFoundException('Aucun user trouvé pour l\'identifiant : ' . $id);
        }

        $entityManager->remove($idee);
        $entityManager->flush();

        return $this->redirectToRoute('idee_list');
    }
}
