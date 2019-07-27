<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\BuyRequest;

/**
 * Build buy request for adding products to cart
 */
interface BuyRequestDataProviderInterface
{
    /**
     * Build buy request for adding product to cart
     *
     * @param array $cartItemData
     * @return DataObject
     */
    public function execute(array $cartItemData): array;
}
