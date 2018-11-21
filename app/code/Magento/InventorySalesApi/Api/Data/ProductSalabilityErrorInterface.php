<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api\Data;

/**
 * @api
 */
interface ProductSalabilityErrorInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ProductSalabilityErrorExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorExtensionInterface $extensionAttributes
    ): void;
}
