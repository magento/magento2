<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\Phrase;

class CartItemValidatorResult implements CartItemValidatorResultInterface
{
    /**
     * @var Phrase[]
     */
    private readonly array $errors;

    /**
     * @param Phrase[]|string[] $errors
     */
    public function __construct(array $errors = [])
    {
        $this->errors = array_map(
            static fn($error) => $error instanceof Phrase ? $error : new Phrase((string)$error),
            $errors,
        );
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
