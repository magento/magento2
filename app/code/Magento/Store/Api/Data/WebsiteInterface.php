<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api\Data;

/**
 * Website interface
 *
 * @api
 * @since 2.0.0
 */
interface WebsiteInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     * Rethreive website name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set website name
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
    public function getDefaultGroupId();

    /**
     * @param int $defaultGroupId
     * @return $this
     * @since 2.0.0
     */
    public function setDefaultGroupId($defaultGroupId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Store\Api\Data\WebsiteExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Store\Api\Data\WebsiteExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\WebsiteExtensionInterface $extensionAttributes
    );
}
