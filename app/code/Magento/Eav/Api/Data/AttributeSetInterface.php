<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

interface AttributeSetInterface
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
}
