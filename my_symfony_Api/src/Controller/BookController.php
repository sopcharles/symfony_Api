<?php
namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/books', name: 'get_books', methods: ['GET'])]
    public function getBooks(): JsonResponse
    {
        $books = $this->entityManager->getRepository(Book::class)->findAll();
        return $this->json($books);
    }

    #[Route('/books/{id}', name: 'get_book', methods: ['GET'])]
    public function getBook(int $id): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['message' => 'Livre non trouvé'], 404);
        }
        return $this->json($book);
    }

    #[Route('/books', name: 'create_book', methods: ['POST'])]
    public function createBook(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $constraint = new Assert\Collection([
            'title' => [new Assert\NotBlank(), new Assert\Length(['max' => 255])],
            'author' => [new Assert\NotBlank(), new Assert\Length(['max' => 255])],
            'publicationYear' => [new Assert\NotBlank(), new Assert\Range(['min' => 1000, 'max' => date('Y')])],
            'isbn' => [new Assert\NotBlank(), new Assert\Isbn()],
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], 400);
        }

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setPublicationYear($data['publicationYear']);
        $book->setIsbn($data['isbn']);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json(["message" => "Livre créé"], 201);
    }

    #[Route('/books/{id}', name: 'update_book', methods: ['PUT'])]
    public function updateBook(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['message' => 'Livre non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $constraint = new Assert\Collection([
            'title' => [new Assert\NotBlank(), new Assert\Length(['max' => 255])],
            'author' => [new Assert\NotBlank(), new Assert\Length(['max' => 255])],
            'publicationYear' => [new Assert\NotBlank(), new Assert\Range(['min' => 1000, 'max' => date('Y')])],
            'isbn' => [new Assert\NotBlank(), new Assert\Isbn()],
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], 400);
        }

        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setPublicationYear($data['publicationYear']);
        $book->setIsbn($data['isbn']);

        $this->entityManager->flush();

        return $this->json($book);
    }

    #[Route('/books/{id}', name: 'delete_book', methods: ['DELETE'])]
    public function deleteBook(int $id): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['message' => 'Livre non trouvé'], 404);
        }

        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return $this->json(['message' => 'Livre supprimé'], 204);
    }
}

