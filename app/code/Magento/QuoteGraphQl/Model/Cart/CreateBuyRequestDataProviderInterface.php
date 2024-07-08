<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
