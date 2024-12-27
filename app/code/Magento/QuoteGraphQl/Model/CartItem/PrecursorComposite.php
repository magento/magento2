<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Chained precursor composite.
 */
class PrecursorComposite implements PrecursorInterface
{
    /**
     * @var PrecursorInterface[]
     */
    private $precursors = [];

    /**
     * @param array $precursors
     */
    public function __construct(array $precursors = [])
    {
        $this->precursors = $precursors;
    }

    /**
     * @inheritdoc
     */
    public function process(array $cartItemData, ContextInterface $context): array
    {
        foreach ($this->precursors as $precursor) {
            $cartItemData = $precursor->process($cartItemData, $context);
        }
        return $cartItemData;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        $errors = [];
        foreach ($this->precursors as $precursor) {
            foreach ($precursor->getErrors() as $error) {
                $errors[] = $error;
            }
        }
        return $errors;
    }
}
