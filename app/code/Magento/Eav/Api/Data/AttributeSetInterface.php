<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

interface AttributeSetInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get attribute set ID
     *
     * @return int|null
     */
    public function getAttributeSetId();

    /**
     * Set attribute set ID
     *
     * @param int $attributeSetId
     * @return $this
     */
    public function setAttributeSetId($attributeSetId);

    /**
     * Get attribute set name
     *
     * @return string
     */
    public function getAttributeSetName();

    /**
     * Set attribute set name
     *
     * @param string $attributeSetName
     * @return $this
     */
    public function setAttributeSetName($attributeSetName);

    /**
     * Get attribute set sort order index
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set attribute set sort order index
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get attribute set entity type id
     *
     * @return int|null
     */
    public function getEntityTypeId();

    /**
     * Set attribute set entity type id
     *
     * @param int $entityTypeId
     * @return $this
     */
    public function setEntityTypeId($entityTypeId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Eav\Api\Data\AttributeSetExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Eav\Api\Data\AttributeSetExtensionInterface|null $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Eav\Api\Data\AttributeSetExtensionInterface $extensionAttributes);
}
