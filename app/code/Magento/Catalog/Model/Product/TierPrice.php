<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

/**
 * @codeCoverageIgnore
 */
class TierPrice extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Catalog\Api\Data\ProductTierPriceInterface
{
    /**
     * Retrieve tier qty
     *
     * @return float
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * Retrieve price value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }
}
