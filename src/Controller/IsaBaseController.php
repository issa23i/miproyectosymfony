<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IsaBaseController extends AbstractController
{
    /**
     * @Route("/isa/base", name="app_isa_base")
     */
    public function index(): Response
    {
        return $this->render('isa_base/index.html.twig', [
            'controller_name' => 'IsaBaseController',
        ]);
    }
}
