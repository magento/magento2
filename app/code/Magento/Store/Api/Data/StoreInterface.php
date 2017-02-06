<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api\Data;

/**
 * Store interface
 *
 * @api
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
