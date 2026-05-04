<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shared\Message\OrderPlacedMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final class OrderPlacedHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $products,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(OrderPlacedMessage $message): void
    {
        $product = $this->products->findOneById(Uuid::fromString($message->productId));

        if (null === $product) {
            $this->logger->warning('OrderPlaced received for unknown product', ['productId' => $message->productId]);

            return;
        }

        $product->setQuantity(max(0, $product->getQuantity() - $message->quantityOrdered));

        $this->em->flush();
    }
}
