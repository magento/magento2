<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Configured price interface
 */
interface ConfiguredPriceInterface
{
    /**
     * Price type configured
     */
    const CONFIGURED_PRICE_CODE = 'configured_price';

    /**
     * Regular price type configured
     */
    const CONFIGURED_REGULAR_PRICE_CODE = 'configured_regular_price';

    /**
     * @param ItemInterface $item
     * @return $this
     */
    public function setItem(ItemInterface $item);
}
