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
 */
interface WebsiteInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     * Rethreive website name
     * 
     * @return string
     */
    public function getName();

    /**
     * Set website name
     * 
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return int
     */
    public function getDefaultGroupId();

    /**
     * @param int $defaultGroupId
     * @return $this
     */
    public function setDefaultGroupId($defaultGroupId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Store\Api\Data\WebsiteExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Store\Api\Data\WebsiteExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\WebsiteExtensionInterface $extensionAttributes
    );
}
