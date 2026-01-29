<?php

namespace App\Controller\Admin;

use App\Entity\Purchase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PurchaseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Purchase::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Achat')
            ->setEntityLabelInPlural('Achats')
            ->setPageTitle('index', '💳 Historique des achats')
            ->setPageTitle('detail', 'Détails de l\'achat')
            ->setDefaultSort(['purchasedAt' => 'DESC'])
            ->setSearchFields(['user.email', 'course.title', 'stripeSessionId'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex();

        yield TextField::new('user.email', 'Utilisateur')
            ->setLabel('Utilisateur');

        if ($pageName === Crud::PAGE_INDEX) {
            yield TextareaField::new('stripePaymentIntentId', 'Article acheté')
                ->formatValue(function ($value, Purchase $entity) {
                    if ($entity->getCourse() !== null) {
                        return '📚 ' . $entity->getCourse()->getTitle();
                    }
                    if ($entity->getLesson() !== null) {
                        return '📖 ' . $entity->getLesson()->getTitle();
                    }
                    return 'Article supprimé';
                })
                ->renderAsHtml();
        }

        yield AssociationField::new('course', 'Cours')
            ->onlyOnDetail()
            ->formatValue(function ($value, $entity) {
                return $entity->getCourse() ? $entity->getCourse()->getTitle() : 'Aucun';
            });

        yield AssociationField::new('lesson', 'Leçon')
            ->onlyOnDetail()
            ->formatValue(function ($value, $entity) {
                return $entity->getLesson() ? $entity->getLesson()->getTitle() : 'Aucune';
            });

        yield TextField::new('amount', 'Montant payé')
            ->formatValue(function ($value, $entity) {
                $amountInEuros = floatval($entity->getAmount());
                return number_format($amountInEuros, 2, ',', ' ') . ' €';
            });

        yield TextField::new('status', 'Statut')
            ->formatValue(function ($value, $entity) {
                $status = $entity->getStatus();
                $badges = [
                    'completed' => '✅ Complété',
                    'pending' => '⏳ En attente',
                    'failed' => '❌ Échoué',
                    'refunded' => '↩️ Remboursé',
                ];
                return $badges[$status] ?? $status;
            });

        yield TextField::new('stripePaymentIntentId', 'ID Stripe')
            ->onlyOnDetail();

        yield DateTimeField::new('purchasedAt', 'Date d\'achat')
            ->setFormat('dd/MM/yyyy HH:mm');
    }
}
