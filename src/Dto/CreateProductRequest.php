<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateProductRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public readonly string $name,

        #[Assert\Positive]
        public readonly float $price,

        #[Assert\PositiveOrZero]
        public readonly int $quantity,
    ) {
    }
}
