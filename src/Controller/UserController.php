<?php

namespace App\Controller;

use App\Entity\Idee;
use App\Entity\User;
use App\Form\UserInfosType;
use App\Form\UserPwdType;
use App\Form\UserType;
use App\Service\UserAccessService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserController extends Controller
{
    /**
     * @Route("/user", name="user_list")
     */
    public function listAction()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findNotById($this->getUser()->getId());

        return $this->render('user/list.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
        ]);
    }

    /**
     * @Route("/user/shopping-list", name="user_shopping_list")
     */
    public function shoppingListAction()
    {
        $idees = $this->getDoctrine()->getRepository(Idee::class)->findAllByUserTaking($this->getUser()->getId(), 0);

        return $this->render('user/shoppingList.html.twig', [
            'idees' => $idees
        ]);
    }

    /**
     * @Route("/user/{id}", name="user_show", requirements={"id"="\d+"})
     */
    public function showAction(User $user, UserAccessService $userAccessService)
    {
        if (!$user) {
            throw $this->createNotFoundException('Aucun user trouvé pour l\'identifiant : ' . $user->getId());
        } elseif ($userAccessService->isConnectedUser($user)) {
            $this->addFlash('danger', 'Bien essayé !');
            return $this->redirectToRoute('user_list');
        }

        $idees = $this->getDoctrine()
            ->getRepository(Idee::class)
            ->findBy(
                [
		            'user' => $user,
                    'archived' => false
        		],
        		['id' => 'DESC']
            );

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'breadcrumb' => [$this->generateUrl('user_list') => "Membres", "" => $user->getFirstname()],
            'idees' => $idees
        ]);
    }

    /**
     * @Route("/createAccount", name="createAccount")
     */
    public function newAction(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
        $user = new User();


        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($em->getRepository(User::class)->findOneBy(['username' => $user->getUsername()])) {
                $this->addFlash('alreadyExists', 'Cet identifiant est déjà pris.');
                return $this->redirectToRoute('createAccount');
            }

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRole('ROLE_USER');

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/new.html.twig', [
           'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/{id}/update", name="user_update", requirements={"id"="\d+"})
     */
    public function updateAction(User $user, UserAccessService $userAccessService) {
        if (!$user || !$userAccessService->isConnectedUser($user)) {
            return $this->redirectToRoute('user_list');
        }

        $formInfos = $this->createForm(UserInfosType::class, $user, [
            'action' => $this->generateUrl('user_update_infos', ['id' => $user->getId()])
        ]);
        $formPwd = $this->createForm(UserPwdType::class, $user, [
            'action' => $this->generateUrl('user_update_password', ['id' => $user->getId()])
        ]);

        return $this->render('user/update.html.twig', [
            'user' => $user,
            'formInfos' => $formInfos->createView(),
            'formPwd' => $formPwd->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}/updateInfos", name="user_update_infos", requirements={"id"="\d+"})
     */
    public function updateInfosAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);

        if (!$user || intval($id) !== $this->getUser()->getId()) {
            return $this->redirectToRoute('user_list');
        }

        $formInfos = $this->createForm(UserInfosType::class, $user, [
            'action' => $this->generateUrl('user_update_infos', ['id' => $id])
        ]);
        $formPwd = $this->createForm(UserPwdType::class, $user, [
            'action' => $this->generateUrl('user_update_password', ['id' => $id])
        ]);

        $formInfos->handleRequest($request);

        if ($formInfos->isSubmitted() && $formInfos->isValid()) {
            $em->flush();

            $this->addFlash('successInfosUpdate', 'Vos informations ont été modifiées avec succès !');
            return $this->redirectToRoute('user_update', ['id' => $id]);
        }

        return $this->render('user/update.html.twig', [
            'user' => $user,
            'formInfos' => $formInfos->createView(),
            'formPwd' => $formPwd->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}/updatePwd", name="user_update_password", requirements={"id"="\d+"})
     */
    public function updatePwdAction(Request $request, $id, UserPasswordEncoderInterface $passwordEncoder) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);

        if (!$user || intval($id) !== $this->getUser()->getId()) {
            return $this->redirectToRoute('user_list');
        }

        $formInfos = $this->createForm(UserInfosType::class, $user, [
            'action' => $this->generateUrl('user_update_infos', ['id' => $id])
        ]);
        $formPwd = $this->createForm(UserPwdType::class, $user, [
            'action' => $this->generateUrl('user_update_password', ['id' => $id])
        ]);

        $formPwd->handleRequest($request);

        if ($formPwd->isSubmitted() && $formPwd->isValid()) {
            if ($user->getPlainPassword() == '') {
                $this->addFlash('dangerPwdUpdate', 'Votre mot de passe ne peut pas être vide.');
            } else {
                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
                $em->flush();

                $this->addFlash('successPwdUpdate', 'Votre mot de passe a été modifié avec succès !');
            }
            return $this->redirectToRoute('user_update', ['id' => $id]);
        }

        return $this->render('user/update.html.twig', [
            'user' => $user,
            'formInfos' => $formInfos->createView(),
            'formPwd' => $formPwd->createView(),
        ]);
    }

    /**
     * @Route("/user/delete/{id}", name="user_delete")
     */
    public function deleteAction(SessionInterface $session, $id = null, AuthorizationCheckerInterface $authChecker) {
        $em = $this->getDoctrine()->getManager();

        // On souhaite supprimer l'utilisateur connecté
        if (is_null($id)) {
            $user = $this->getUser();
            if (count($user->getIdees())) {
                $this->addFlash('danger', 'Vous avez des idées. Supprimez d\'abord vos idées pour supprimer votre compte.');
                return $this->redirectToRoute('user_update', ['id' => $user->getId()]);
            }
            $this->get('security.token_storage')->setToken(null);
            $session->invalidate();
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
            return $this->redirectToRoute('login');
        // On souhaite supprimer l'utilisateur $id
        } else {
            // Si l'utilisateur n'est pas ADMIN
            if (!$authChecker->isGranted('ROLE_ADMIN')) {
                $this->addFlash('danger', 'Vous n\'avez pas les droits suffisants pour effectuer cette opération.');
                return $this->redirectToRoute('user_list');
            }
            $user = $em->getRepository(User::class)->find($id);
            if (count($user->getIdees())) {
                $this->addFlash('danger', 'Ce membre a des idées. Supprimez d\'abord ses idées pour supprimer son compte.');
            } else {
                $this->addFlash('success', 'Le compte de ' . $user->getFullname() . ' a été supprimé avec succès.');
                $em->remove($user);
                $em->flush();
            }
            return $this->redirectToRoute('user_list');
        }
    }

    /**
     * @Route("/user/deleteIdees", name="user_idees_delete")
     */
    public function deleteIdeesAction() {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository(User::class)->deleteAllIdees($this->getUser());

        $this->addFlash('success', 'Vos idées ont bien été supprimées !');
        return $this->redirectToRoute('idee_list');
    }
}
