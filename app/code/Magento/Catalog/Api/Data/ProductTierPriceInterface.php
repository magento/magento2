<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @todo remove this interface if framework support return array
 */
interface ProductTierPriceInterface
{
    const QTY = 'qty';

    const VALUE = 'value';

    /**
     * Retrieve tier qty
     *
     * @return float
     */
    public function getQty();

    /**
     * Retrieve price value
     *
     * @return float
     */
    public function getValue();
}
