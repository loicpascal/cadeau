<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function admin(Request $request)
    {
        return new Response(
            '<html><body>ADMIN !</body></html>'
        );
    }
}