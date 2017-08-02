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
 * @since 2.0.0
 */
interface GroupInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
    public function getRootCategoryId();

    /**
     * @param int $rootCategoryId
     * @return $this
     * @since 2.0.0
     */
    public function setRootCategoryId($rootCategoryId);

    /**
     * @return int
     * @since 2.0.0
     */
    public function getDefaultStoreId();

    /**
     * @param int $defaultStoreId
     * @return $this
     * @since 2.0.0
     */
    public function setDefaultStoreId($defaultStoreId);

    /**
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Retrieves group code.
     * Group code is a unique field.
     *
     * @return string
     * @since 2.2.0
     */
    public function getCode();

    /**
     * Sets group code.
     *
     * @param string $code
     * @return $this
     * @since 2.2.0
     */
    public function setCode($code);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Store\Api\Data\GroupExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Store\Api\Data\GroupExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\GroupExtensionInterface $extensionAttributes
    );
}
