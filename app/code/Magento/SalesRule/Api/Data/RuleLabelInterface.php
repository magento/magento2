<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api\Data;

/**
 * Interface RuleLabelInterface
 *
 * @api
 * @since 2.0.0
 */
interface RuleLabelInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get storeId
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($storeId);

    /**
     * Return the label for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getStoreLabel();

    /**
     * Set the label for the store
     *
     * @param string $storeLabel
     * @return $this
     * @since 2.0.0
     */
    public function setStoreLabel($storeLabel);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SalesRule\Api\Data\RuleLabelExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SalesRule\Api\Data\RuleLabelExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\RuleLabelExtensionInterface $extensionAttributes
    );
}
