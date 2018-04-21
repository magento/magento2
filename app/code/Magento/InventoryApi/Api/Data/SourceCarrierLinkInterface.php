<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents relation between some physical storage and shipping method
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
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
    const SOURCE_CODE = 'source_code';
    /**#@-*/

    /**
     * Get carrier code
     *
     * @return string|null
     */
    public function getCarrierCode();

    /**
     * Set carrier code
     *
     * @param string|null $carrierCode
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
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(SourceCarrierLinkExtensionInterface $extensionAttributes);
}
