<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\Theme;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EasyAdminDashboardController extends AbstractDashboardController
{
    #[Route('/admin/crud', name: 'admin_crud')]
    public function index(): Response
    {
        return $this->render('admin/easyadmin_home.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Knowledge Learning - CRUD')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard Principal', 'fa fa-home')
            ->setLinkRel('app_admin_dashboard');

        yield MenuItem::section('👥 Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);

        yield MenuItem::section('📚 Contenu pédagogique');
        yield MenuItem::linkToCrud('Thèmes', 'fa fa-palette', Theme::class);
        yield MenuItem::linkToCrud('Cours', 'fa fa-book', Course::class);
        yield MenuItem::linkToCrud('Leçons', 'fa fa-file-alt', Lesson::class);

        yield MenuItem::section('💳 Commerce');
        yield MenuItem::linkToCrud('Achats', 'fa fa-shopping-cart', Purchase::class);

        yield MenuItem::section('🔙 Retour');
        yield MenuItem::linkToRoute('Dashboard Admin', 'fa fa-tachometer-alt', 'app_admin_dashboard');
        yield MenuItem::linkToRoute('Voir le site', 'fa fa-home', 'app_home');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out-alt');
    }
}
