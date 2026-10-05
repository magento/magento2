<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Webapi;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Validator\ServiceInputValidatorInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Validate cart item quantity before the quote item setter normalizes it.
 */
class CartItemQtyValidator implements ServiceInputValidatorInterface
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    public function validateComplexArrayType(string $className, array $items): void
    {
        // Cart item quantity is a scalar value.
    }

    /**
     * @inheritdoc
     */
    public function validateEntityValue(object $entity, string $propertyName, $value): void
    {
        if ($entity instanceof CartItemInterface
            && $propertyName === 'qty'
            && (!is_numeric($value) || $value <= 0)
        ) {
            throw InputException::invalidFieldValue('qty', $value);
        }
    }
}
