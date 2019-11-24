<?php

namespace App\Controller;

use App\Entity\Param;
use App\Form\ParamType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ParamController extends Controller
{
    /**
     * @Route("/param", name="param")
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $param = $em->getRepository(Param::class)->find(1);

        if (!$param) {
            throw $this->createNotFoundException('Objet Param non trouvé pour l\'identifiant ' . 1);
        }

        $form = $this->createForm(ParamType::class, $param);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Les modifications ont bien été prises en compte');
            return $this->redirectToRoute('param');
        }

        return $this->render('param/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
