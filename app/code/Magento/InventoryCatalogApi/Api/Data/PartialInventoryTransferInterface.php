<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Api\Data;

/**
 * @api
 *
 * DTO to handle partial stock transfers from origin source to destination source,
 * specifying the quantity to be transferred.
 * @see \Magento\InventoryCatalogApi\Api\BulkPartialInventoryTransferInterface
 */
interface PartialInventoryTransferInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const SKU                     = 'sku';
    const QTY                     = 'qty';
    const ORIGIN_SOURCE_CODE      = 'origin_source_code';
    const DESTINATION_SOURCE_CODE = 'destination_source_code';

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @param string $sku
     */
    public function setSku(string $sku): void;

    /**
     * @return float
     */
    public function getQty(): float;

    /**
     * @param float $qty
     */
    public function setQty(float $qty): void;

    /**
     * @return string
     */
    public function getOriginSourceCode(): string;

    /**
     * @param string $code
     */
    public function setOriginSourceCode(string $code): void;

    /**
     * @return string
     */
    public function getDestinationSourceCode(): string;

    /**
     * @param string $code
     */
    public function setDestinationSourceCode(string $code): void;

    /**
     * @return \Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferExtensionInterface;

    /**
     * @param PartialInventoryTransferExtensionInterface $extensionAttributes
     */
    public function setExtensionAttributes(\Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferExtensionInterface $extensionAttributes): void;
}