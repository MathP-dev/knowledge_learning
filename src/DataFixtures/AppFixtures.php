<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {

        $admin = new User();
        $admin->setFirstName('Admin');
        $admin->setLastName('Knowledge');
        $admin->setEmail('admin@knowledge-learning.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        $admin->setVerified(true);
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $manager->persist($admin);

        $user = new User();
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setEmail('jean.dupont@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'User123!'));
        $user->setVerified(true);
        $manager->persist($user);

        $data = [
            'Musique' => [
                [
                    'title' => 'Cursus d\'initiation à la guitare',
                    'price' => '50',
                    'lessons' => [
                        ['title' => 'Découverte de l\'instrument', 'price' => '26'],
                        ['title' => 'Les accords et les gammes', 'price' => '26'],
                    ],
                ],
                [
                    'title' => 'Cursus d\'initiation au piano',
                    'price' => '50',
                    'lessons' => [
                        ['title' => 'Découverte de l\'instrument', 'price' => '26'],
                        ['title' => 'Les accords et les gammes', 'price' => '26'],
                    ],
                ],
            ],
            'Informatique' => [
                [
                    'title' => 'Cursus d\'initiation au développement web',
                    'price' => '60',
                    'lessons' => [
                        ['title' => 'Les langages Html et CSS', 'price' => '32'],
                        ['title' => 'Dynamiser votre site avec Javascript', 'price' => '32'],
                    ],
                ],
            ],
            'Jardinage' => [
                [
                    'title' => 'Cursus d\'initiation au jardinage',
                    'price' => '30',
                    'lessons' => [
                        ['title' => 'Les outils du jardinier', 'price' => '16'],
                        ['title' => 'Jardiner avec la lune', 'price' => '16'],
                    ],
                ],
            ],
            'Cuisine' => [
                [
                    'title' => 'Cursus d\'initiation à la cuisine',
                    'price' => '44',
                    'lessons' => [
                        ['title' => 'Les modes de cuisson', 'price' => '23'],
                        ['title' => 'Les saveurs', 'price' => '23'],
                    ],
                ],
                [
                    'title' => 'Cursus d\'initiation à l\'art du dressage culinaire',
                    'price' => '48',
                    'lessons' => [
                        ['title' => 'Mettre en œuvre le style dans l\'assiette', 'price' => '26'],
                        ['title' => 'Harmoniser un repas à quatre plats', 'price' => '26'],
                    ],
                ],
            ],
        ];

        foreach ($data as $themeName => $courses) {
            $theme = new Theme();
            $theme->setName($themeName);
            $theme->setSlug($this->slugger->slug($themeName)->lower()->toString());
            $theme->setDescription('Découvrez nos formations en ' . strtolower($themeName));
            $manager->persist($theme);

            foreach ($courses as $courseData) {
                $course = new Course();
                $course->setTitle($courseData['title']);
                $course->setSlug($this->slugger->slug($courseData['title'])->lower()->toString());
                $course->setDescription('Un cursus complet pour maîtriser les fondamentaux.');
                $course->setPrice($courseData['price']);
                $course->setTheme($theme);
                $manager->persist($course);

                $position = 1;
                foreach ($courseData['lessons'] as $lessonData) {
                    $lesson = new Lesson();
                    $lesson->setTitle($lessonData['title']);
                    $lesson->setSlug($this->slugger->slug($courseData['title'] . ' ' . $lessonData['title'])->lower()->toString());
                    $lesson->setContent($this->getLoremIpsum());
                    $lesson->setPrice($lessonData['price']);
                    $lesson->setPosition($position++);
                    $lesson->setCourse($course);
                    $manager->persist($lesson);
                }
            }
        }

        $manager->flush();
    }

    private function getLoremIpsum(): string
    {
        return '<h3>Introduction</h3>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>

<h3>Contenu principal</h3>
<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.  Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>

<h3>Points clés</h3>
<ul>
    <li>Point important numéro 1</li>
    <li>Point important numéro 2</li>
    <li>Point important numéro 3</li>
</ul>

<h3>Conclusion</h3>
<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>';
    }
}
