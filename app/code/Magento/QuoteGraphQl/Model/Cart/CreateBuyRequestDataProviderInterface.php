<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

interface CreateBuyRequestDataProviderInterface
{
    /**
     * Create buy request data that can be used for working with cart items
     *
     * @param array $cartItemData
     * @return array
     */
    public function execute(array $cartItemData): array;
}
