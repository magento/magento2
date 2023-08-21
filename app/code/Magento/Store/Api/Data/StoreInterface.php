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
 * @since 100.0.2
 */
interface StoreInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Retrieve store name
     *
     * @return string
     */
    public function getName();

    /**
     * Set store name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return int
     */
    public function getWebsiteId();

    /**
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * @return int
     */
    public function getStoreGroupId();

    /**
     * @param int $isActive
     * @return $this
     * @since 101.0.0
     */
    public function setIsActive($isActive);

    /**
     * @return int
     * @since 101.0.0
     */
    public function getIsActive();

    /**
     * @param int $storeGroupId
     * @return $this
     */
    public function setStoreGroupId($storeGroupId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Store\Api\Data\StoreExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Store\Api\Data\StoreExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\StoreExtensionInterface $extensionAttributes
    );
}
