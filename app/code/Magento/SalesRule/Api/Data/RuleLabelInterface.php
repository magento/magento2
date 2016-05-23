<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api\Data;

/**
 * Interface RuleLabelInterface
 *
 * @api
 */
interface RuleLabelInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get storeId
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Return the label for the store
     *
     * @return string
     */
    public function getStoreLabel();

    /**
     * Set the label for the store
     *
     * @param string $storeLabel
     * @return $this
     */
    public function setStoreLabel($storeLabel);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SalesRule\Api\Data\RuleLabelExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SalesRule\Api\Data\RuleLabelExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\RuleLabelExtensionInterface $extensionAttributes
    );
}
