<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\Processor;

use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Process Cart Item Data
 */
interface ItemDataProcessorInterface
{
    /**
     * Process cart item data
     *
     * @param array $cartItemData
     * @param ContextInterface $context
     * @return array
     */
    public function process(array $cartItemData, ContextInterface $context): array;
}
