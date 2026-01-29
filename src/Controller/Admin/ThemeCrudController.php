<?php

namespace App\Controller\Admin;

use App\Entity\Theme;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ThemeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Theme::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Thème')
            ->setEntityLabelInPlural('Thèmes')
            ->setPageTitle('index', '🎨 Gestion des thèmes')
            ->setPageTitle('new', 'Créer un thème')
            ->setPageTitle('edit', 'Modifier un thème')
            ->setPageTitle('detail', 'Détails du thème')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['name', 'slug', 'description'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setLabel('Créer un thème')
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

        yield TextField::new('name', 'Nom')
            ->setRequired(true)
            ->setHelp('Le nom du thème tel qu\'il apparaîtra aux utilisateurs');

        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName('name')
            ->setHelp('URL amicale générée automatiquement');

        yield TextareaField::new('description', 'Description')
            ->hideOnIndex()
            ->setHelp('Description détaillée du thème');


        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('coursesCount', 'Nombre de cours')
                ->formatValue(function ($value, $entity) {
                    $count = $entity->getCourses()->count();
                    if ($count === 0) {
                        return 'Aucun cours';
                    }
                    return $count . ' cours';
                })
                ->setVirtual(true);
        }
    }
}
