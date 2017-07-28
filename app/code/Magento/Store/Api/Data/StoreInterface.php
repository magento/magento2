<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api\Data;

/**
 * Store interface
 *
 * @api
 * @since 2.0.0
 */
interface StoreInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return int
     * @since 2.0.0
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Retrieve store name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set store name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * @return int
     * @since 2.0.0
     */
    public function getWebsiteId();

    /**
     * @param int $websiteId
     * @return $this
     * @since 2.0.0
     */
    public function setWebsiteId($websiteId);

    /**
     * @return int
     * @since 2.0.0
     */
    public function getStoreGroupId();

    /**
     * @param int $storeGroupId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreGroupId($storeGroupId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Store\Api\Data\StoreExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Store\Api\Data\StoreExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\StoreExtensionInterface $extensionAttributes
    );
}
