<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
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
