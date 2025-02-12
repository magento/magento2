<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\BuyRequest;

/**
 * Build buy request for adding products to cart
 *
 * @api
 */
interface BuyRequestDataProviderInterface
{
    /**
     * Provide buy request data from add to cart item request
     *
     * @param array $cartItemData
     * @return array
     */
    public function execute(array $cartItemData): array;
}
