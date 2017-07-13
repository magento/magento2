<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents relation between some physical storage and shipping method
 *
 * @api
 */
interface SourceCarrierLinkInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const CARRIER_CODE = 'carrier_code';
    const POSITION = 'position';
    /**#@-*/

    /**
     * Get carrier code
     *
     * @return string
     */
    public function getCarrierCode();

    /**
     * Set carrier code
     *
     * @param string $carrierCode
     * @return void
     */
    public function setCarrierCode($carrierCode);

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param int|null $position
     * @return void
     */
    public function setPosition($position);

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface $extensionAttributes
    );
}
