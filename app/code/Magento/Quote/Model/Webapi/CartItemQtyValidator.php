<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Webapi;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Validator\ServiceInputValidatorInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

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
        if (($entity instanceof CartItemInterface || $entity instanceof AbstractItem)
            && $propertyName === 'qty'
            && (!is_numeric($value) || $value <= 0)
        ) {
            throw new InputException(
                new Phrase(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['fieldName' => 'qty', 'value' => $value]
                )
            );
        }
    }
}
