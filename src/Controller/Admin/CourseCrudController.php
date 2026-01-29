<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CourseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Course::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Cours')
            ->setEntityLabelInPlural('Cours')
            ->setPageTitle('index', '📚 Gestion des cours')
            ->setPageTitle('new', 'Créer un cours')
            ->setPageTitle('edit', 'Modifier un cours')
            ->setPageTitle('detail', 'Détails du cours')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['title', 'slug', 'description'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setLabel('Créer un cours')
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
            ->setHelp('Le titre du cours tel qu\'il apparaîtra aux utilisateurs');

        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName('title')
            ->setHelp('URL amicale générée automatiquement');

        yield TextareaField::new('description', 'Description')
            ->hideOnIndex()
            ->setHelp('Description détaillée du cours');

        yield MoneyField::new('price', 'Prix')
            ->setCurrency('EUR')
            ->setRequired(true)
            ->setHelp('Prix du cours en euros');

        yield AssociationField::new('theme', 'Thème')
            ->setRequired(true)
            ->setHelp('Catégorie à laquelle appartient le cours');

        yield DateTimeField::new('createdAt', 'Date de création')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        // Afficher le nombre de leçons UNIQUEMENT dans la vue détails
        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('lessonsCount', 'Nombre de leçons')
                ->formatValue(function ($value, $entity) {
                    $count = $entity->getLessons()->count();
                    if ($count === 0) {
                        return 'Aucune leçon';
                    }
                    return $count . ' leçon' . ($count > 1 ? 's' : '');
                })
                ->setVirtual(true);

            yield TextField::new('lessonsList', 'Liste des leçons')
                ->formatValue(function ($value, $entity) {
                    $lessons = $entity->getLessons();
                    if ($lessons->count() === 0) {
                        return '<em>Aucune leçon pour le moment</em>';
                    }
                    $html = '<ul class="list-unstyled">';
                    foreach ($lessons as $lesson) {
                        $html .= '<li>📖 ' . htmlspecialchars($lesson->getTitle()) . ' <small class="text-muted">(Position: ' . $lesson->getPosition() . ')</small></li>';
                    }
                    $html .= '</ul>';
                    return $html;
                })
                ->setVirtual(true);
        }
    }
}

