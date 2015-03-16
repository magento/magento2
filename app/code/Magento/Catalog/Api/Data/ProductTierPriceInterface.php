<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @todo remove this interface if framework support return array
 */
interface ProductTierPriceInterface extends ExtensibleDataInterface
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
     * Set tier qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Retrieve price value
     *
     * @return float
     */
    public function getValue();

    /**
     * Set price value
     *
     * @param float $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface $extensionAttributes
    );
}
