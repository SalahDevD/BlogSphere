<?php

namespace App\Controller\Supervisor;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/supervisor', name: 'supervisor_')]
#[IsGranted('ROLE_SUPERVISOR')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render('supervisor/dashboard.html.twig');
    }
}
