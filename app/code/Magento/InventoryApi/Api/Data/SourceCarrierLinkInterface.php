<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

/**
 * Represents relation between some physical storage and shipping method
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceCarrierLinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
    public function getCarrierCode(): ?string;

    /**
     * Set carrier code
     *
     * @param string|null $carrierCode
     * @return void
     */
    public function setCarrierCode(?string $carrierCode): void;

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition(): ?int;

    /**
     * Set position
     *
     * @param int|null $position
     * @return void
     */
    public function setPosition(?int $position): void;

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface|null
     */
    public function getExtensionAttributes(): ?\Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface $extensionAttributes
    ): void;
}
