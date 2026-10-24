<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly string $productName,
        public readonly int $requested,
        public readonly int $available
    ) {
        parent::__construct('Stock insuficiente para completar la orden.');
    }
}
