<?php


namespace App\ApiResource;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="API Bibliothèque",
 *     version="1.0.0",
 *     description="Une API pour gérer une bibliothèque"
 * )
 * @OA\Schema(
 *     schema="Book",
 *     type="object",
 *     title="Book",
 *     required={"title", "author", "publicationYear", "isbn"},
 *     properties={
 *         @OA\Property(property="id", type="integer", readOnly=true),
 *         @OA\Property(property="title", type="string"),
 *         @OA\Property(property="author", type="string"),
 *         @OA\Property(property="publicationYear", type="integer"),
 *         @OA\Property(property="isbn", type="string")
 *     }
 * )
 */
class ApiBookController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/books", name="get_books", methods={"GET"})
     * @OA\Get(
     *     path="/books",
     *     summary="Récupérer la liste de tous les livres",
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))
     *     )
     * )
     */
    public function getBooks(): JsonResponse
    {
        $books = $this->entityManager->getRepository(Book::class)->findAll();
        return $this->json($books);
    }

    /**
     * @Route("/books/{id}", name="get_book", methods={"GET"})
     * @OA\Get(
     *     path="/books/{id}",
     *     summary="Récupérer les détails d'un livre spécifique",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du livre à récupérer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Livre non trouvé"
     *     )
     * )
     */
    public function getBook(int $id): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['message' => 'Livre non trouvé'], 404);
        }
        return $this->json($book);
    }

    /**
     * @Route("/books", name="create_book", methods={"POST"})
     * @OA\Post(
     *     path="/books",
     *     summary="Créer un nouveau livre",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="author", type="string"),
     *             @OA\Property(property="publicationYear", type="integer"),
     *             @OA\Property(property="isbn", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Livre créé",
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     )
     * )
     */
    public function createBook(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
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

        // Création du livre
        $book = new Book();
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setPublicationYear($data['publicationYear']);
        $book->setIsbn($data['isbn']);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json($book, 201);
    }

    /**
     * @Route("/books/{id}", name="update_book", methods={"PUT"})
     * @OA\Put(
     *     path="/books/{id}",
     *     summary="Mettre à jour les informations d'un livre",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du livre à mettre à jour",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="author", type="string"),
     *             @OA\Property(property="publicationYear", type="integer"),
     *             @OA\Property(property="isbn", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Livre mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Livre non trouvé"
     *     )
     * )
     */
    public function updateBook(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['message' => 'Livre non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Validation des données
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

        // Mise à jour du livre
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setPublicationYear($data['publicationYear']);
        $book->setIsbn($data['isbn']);

        $this->entityManager->flush();

        return $this->json($book);
    }

    /**
     * @Route("/books/{id}", name="delete_book", methods={"DELETE"})
     * @OA\Delete(
     *     path="/books/{id}",
     *     summary="Supprimer un livre",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du livre à supprimer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Livre supprimé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Livre non trouvé"
     *     )
     * )
     */
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
