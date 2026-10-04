<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\Processor;

use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Process Cart Item Data
 *
 * @api
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
