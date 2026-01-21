<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Course;
use App\Entity\Theme;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CourseRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private CourseRepository $courseRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->courseRepository = $this->entityManager->getRepository(Course::class);
    }

    public function testFindBySlug(): void
    {
        // Arrange
        $theme = $this->createTheme();
        $slug = 'test-course-' . uniqid();

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setSlug($slug);
        $course->setPrice('50.00');
        $course->setTheme($theme);

        $this->entityManager->persist($course);
        $this->entityManager->flush();

        // Act
        $foundCourse = $this->courseRepository->findBySlug($slug);

        // Assert
        $this->assertInstanceOf(Course::class, $foundCourse);
        $this->assertEquals($slug, $foundCourse->getSlug());
        $this->assertEquals($course->getId(), $foundCourse->getId());
    }

    public function testFindByTheme(): void
    {
        // Arrange
        $theme = $this->createTheme();

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setSlug('course-1-' . uniqid());
        $course1->setPrice('50.00');
        $course1->setTheme($theme);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setSlug('course-2-' . uniqid());
        $course2->setPrice('60.00');
        $course2->setTheme($theme);

        $this->entityManager->persist($course1);
        $this->entityManager->persist($course2);
        $this->entityManager->flush();

        // Act
        $courses = $this->courseRepository->findByTheme($theme);

        // Assert
        $this->assertIsArray($courses);
        $this->assertGreaterThanOrEqual(2, count($courses));
    }

    public function testSaveCourse(): void
    {
        // Arrange
        $theme = $this->createTheme();

        $course = new Course();
        $course->setTitle('Save Test');
        $course->setSlug('save-test-' . uniqid());
        $course->setPrice('40.00');
        $course->setTheme($theme);

        // Act
        $this->courseRepository->save($course, true);

        // Assert
        $this->assertNotNull($course->getId());
        $foundCourse = $this->courseRepository->find($course->getId());
        $this->assertEquals($course->getTitle(), $foundCourse->getTitle());
    }

    private function createTheme(): Theme
    {
        $theme = new Theme();
        $theme->setName('Test Theme ' . uniqid());
        $theme->setSlug('test-theme-' . uniqid());

        $this->entityManager->persist($theme);
        $this->entityManager->flush();

        return $theme;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}