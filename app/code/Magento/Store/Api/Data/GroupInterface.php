<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api\Data;

/**
 * Group interface
 *
 * @api
 * @since 100.0.2
 */
interface GroupInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
    public function getRootCategoryId();

    /**
     * @param int $rootCategoryId
     * @return $this
     */
    public function setRootCategoryId($rootCategoryId);

    /**
     * @return int
     */
    public function getDefaultStoreId();

    /**
     * @param int $defaultStoreId
     * @return $this
     */
    public function setDefaultStoreId($defaultStoreId);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Retrieves group code.
     * Group code is a unique field.
     *
     * @return string
     * @since 100.2.0
     */
    public function getCode();

    /**
     * Sets group code.
     *
     * @param string $code
     * @return $this
     * @since 100.2.0
     */
    public function setCode($code);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Store\Api\Data\GroupExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Store\Api\Data\GroupExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\GroupExtensionInterface $extensionAttributes
    );
}
