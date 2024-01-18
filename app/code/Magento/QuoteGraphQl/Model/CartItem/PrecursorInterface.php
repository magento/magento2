<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\Cart\Data\CartItem;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\Processor\ItemDataProcessorInterface;

/**
 * Cart items preparator for cart operations.
 */
interface PrecursorInterface extends ItemDataProcessorInterface
{
    /**
     * Preprocess cart items for Graphql request.
     *
     * @param array $cartItemData
     * @param ContextInterface $context
     *
     * @return array
     */
    public function process(array $cartItemData, ContextInterface $context): array;

    /**
     * Get precursor errors.
     *
     * @return array
     */
    public function getErrors(): array;
}
