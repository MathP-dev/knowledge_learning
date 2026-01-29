<?php

namespace App\Controller\Admin;

use App\Entity\Lesson;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LessonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Lesson::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Leçon')
            ->setEntityLabelInPlural('Leçons')
            ->setPageTitle('index', '📖 Gestion des leçons')
            ->setPageTitle('new', 'Créer une leçon')
            ->setPageTitle('edit', 'Modifier une leçon')
            ->setPageTitle('detail', 'Détails de la leçon')
            ->setDefaultSort(['course' => 'ASC', 'position' => 'ASC'])
            ->setSearchFields(['title', 'slug', 'content'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setLabel('Créer une leçon')
                    ->setIcon('fa fa-plus');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setLabel('Modifier')
                    ->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setLabel('Supprimer')
                    ->setIcon('fa fa-trash');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex();

        yield TextField::new('title', 'Titre')
            ->setRequired(true)
            ->setHelp('Le titre de la leçon');

        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName('title')
            ->setHelp('URL amicale générée automatiquement');

        yield TextareaField::new('content', 'Contenu')
            ->hideOnIndex()
            ->setHelp('Contenu de la leçon');

        yield IntegerField::new('position', 'Position')
            ->setRequired(true)
            ->setHelp('Ordre d\'affichage dans le cours (commence à 1)');

        yield AssociationField::new('course', 'Cours')
            ->setRequired(true)
            ->setHelp('Cours auquel appartient cette leçon');

        yield DateTimeField::new('createdAt', 'Date de création')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');
    }
}
