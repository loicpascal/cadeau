<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Idee;
use App\Entity\User;
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
    public function newAction(Request $request, $id_idee)
    {
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

            /**
             * On envoie un mail au membre concerné seulement si :
             * - le membre souhaite recevoir des notifications par mail
             * - le membre de l'idée est le membre qui l'a déposée
             */
            if ($idee->getUser()->getReceiveEmailNewComment() && ($idee->getUser()->getId() && !$idee->getUserAdding())) {
                $this->sendEmailNewComment($user, $idee, $comment, $request);
            }

            return $this->redirectToRoute(
                ($this->getUser()->getId() == $idee->getUser()->getId()) ? 'idee_update' : 'idee_show',
                ['id' => $id_idee]
            );
        }
        return $this->render('comment/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/comment/{id}/delete", name="comment_delete", requirements={"id"="\d+"})
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository(Comment::class)->find($id);
        $id_idee = $comment->getIdee()->getId();

        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute(
            ($this->getUser()-> getId() == $comment->getIdee()->getUser()->getId()) ? 'idee_update' : 'idee_show',
            ['id' => $id_idee]
        );
    }

    private function sendEmailNewComment(User $user, $idee, Comment $comment, $request)
    {
        $urlSite = $request->getScheme() . '://' . $request->getHttpHost();

        $body = '<p>Bonjour,</p>';
        $body .= $user->getFirstname() . " a ajouté un commentaire sur votre idée <b>" . $idee->getLibelle() . "</b>.";
        $body .= '<br><br><b>Commentaire :</b><br>';
        $body .= '---<br>' . nl2br(htmlspecialchars($comment->getContent())) . '<br>---';

        $link = $urlSite . $this->generateUrl('idee_update', ['id' => $idee->getId()]);
        $body .= '<p><a href="' . $link . '">Répondre au commentaire</a></p>';
        $body .= '<p>À bientôt sur votre site !<br><a href="' . $urlSite . '">' . $request->getHttpHost() . '</a></p>';

        $message = (new \Swift_Message('Ma lettre au père Noël - Nouveau commentaire'))
            ->setFrom('nepasrepondre@loic-pascal.fr')
            ->setTo($idee->getUser()->getEmail())
            ->setBody(
                $body,
                'text/html'
            );
        $this->get('mailer')->send($message);
    }
}
