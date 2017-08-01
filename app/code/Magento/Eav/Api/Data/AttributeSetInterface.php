<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeSetInterface
 * @api
 * @since 2.0.0
 */
interface AttributeSetInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get attribute set ID
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getAttributeSetId();

    /**
     * Set attribute set ID
     *
     * @param int $attributeSetId
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeSetId($attributeSetId);

    /**
     * Get attribute set name
     *
     * @return string
     * @since 2.0.0
     */
    public function getAttributeSetName();

    /**
     * Set attribute set name
     *
     * @param string $attributeSetName
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeSetName($attributeSetName);

    /**
     * Get attribute set sort order index
     *
     * @return int
     * @since 2.0.0
     */
    public function getSortOrder();

    /**
     * Set attribute set sort order index
     *
     * @param int $sortOrder
     * @return $this
     * @since 2.0.0
     */
    public function setSortOrder($sortOrder);

    /**
     * Get attribute set entity type id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getEntityTypeId();

    /**
     * Set attribute set entity type id
     *
     * @param int $entityTypeId
     * @return $this
     * @since 2.0.0
     */
    public function setEntityTypeId($entityTypeId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Eav\Api\Data\AttributeSetExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Eav\Api\Data\AttributeSetExtensionInterface|null $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Eav\Api\Data\AttributeSetExtensionInterface $extensionAttributes);
}
