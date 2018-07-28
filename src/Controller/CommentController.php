<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Idee;
use App\Form\CommentType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CommentController extends Controller
{
    /**
     * @Route("/comment/new/{id_idee}", name="comment_new")
     */
    public function newAction(Request $request, $id_idee) {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();

            $user = $this->getUser();
            $idee = $this->getDoctrine()->getRepository(Idee::class)->find($id_idee);
            $em = $this->getDoctrine()->getManager();

            $comment->setUser($user);
            $comment->setIdee($idee);
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('idee_show', ['id' => $id_idee]);
        }
        return $this->render('comment/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/comment/{id}/delete", name="comment_delete", requirements={"id"="\d+"})
     */
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository(Comment::class)->find($id);
        $id_idee = $comment->getIdee()->getId();

        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute('idee_show', ['id' => $id_idee]);
    }
}
