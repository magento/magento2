<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api\Data;

interface ConfigurableItemOptionValueInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get option SKU
     *
     * @return string
     */
    public function getSku();

    /**
     * Set option SKU
     *
     * @param string $value
     * @return void
     */
    public function setSku($value);

    /**
     * Get item id
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * Set item id
     *
     * @param int|null $value
     * @return void
     */
    public function setItemId($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface $extensionAttributes
    );
}
