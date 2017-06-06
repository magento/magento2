<?php

namespace Magento\CatalogInventory\Api\Data;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Editable representation of inventory configuration interface
 *
 * In case if value is null, it means that parent value is going to be used
 *
 * @api
 */
interface WarehouseRecordConfigurationInterface
    extends WarehouseRecordConfigurationDataInterface, ExtensibleDataInterface
{
    /**
     * Sets warehouse record identifier
     *
     * @param int $identifier
     * @return $this
     */
    public function setWarehouseRecordId($identifier);

    /**
     * Returns warehouse record identifier
     *
     * @return int
     */
    public function getWarehouseRecordId();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogInventory\Api\Data\WarehouseRecordConfigurationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogInventory\Api\Data\WarehouseRecordConfigurationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\WarehouseRecordConfigurationExtensionInterface $extensionAttributes
    );
}
