<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateProductRequest;
use App\Dto\UpdateProductRequest;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Dto\ProductDto;
use Shared\Service\ProductPublisher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $products,
        private readonly ProductPublisher $publisher,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateProductRequest $request): JsonResponse
    {
        $product = new Product($request->name, $request->price, $request->quantity);

        $this->em->persist($product);
        $this->em->flush();

        $this->publisher->publish($product);

        return new JsonResponse(ProductDto::fromEntity($product), 201);
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = array_map(
            static fn (Product $p) => ProductDto::fromEntity($p),
            $this->products->findAll(),
        );

        return new JsonResponse(['data' => $items]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return new JsonResponse(['error' => 'invalid id'], 400);
        }

        $product = $this->products->findOneById(Uuid::fromString($id));
        if (null === $product) {
            return new JsonResponse(['error' => 'not found'], 404);
        }

        return new JsonResponse(ProductDto::fromEntity($product));
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, #[MapRequestPayload] UpdateProductRequest $request): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return new JsonResponse(['error' => 'invalid id'], 400);
        }

        $product = $this->products->findOneById(Uuid::fromString($id));
        if (null === $product) {
            return new JsonResponse(['error' => 'not found'], 404);
        }

        $product->setName($request->name);
        $product->setPrice($request->price);
        $product->setQuantity($request->quantity);

        $this->em->flush();

        $this->publisher->publish($product);

        return new JsonResponse(ProductDto::fromEntity($product));
    }
}
