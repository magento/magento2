<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\Phrase;

interface CartItemValidatorResultInterface
{
    /**
     * Get errors.
     *
     * @return Phrase[]
     */
    public function getErrors(): array;
}
