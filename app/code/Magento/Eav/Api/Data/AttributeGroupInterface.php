<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface AttributeGroupInterface extends ExtensibleDataInterface
{
    const GROUP_ID = 'attribute_group_id';
    const GROUP_NAME = 'attribute_group_name';
    const ATTRIBUTE_SET_ID = 'attribute_set_id';
    const SORT_ORDER = 'sort_order';
    const DEFAULT_ID = 'default_id';
    const ATTRIBUTE_GROUP_CODE = 'attribute_group_code';
    const SCOPE_CODE = 'tab_group_code';

    /**
     * Retrieve id
     *
     * @return string|null
     */
    public function getAttributeGroupId();

    /**
     * Set id
     *
     * @param string $attributeGroupId
     * @return $this
     */
    public function setAttributeGroupId($attributeGroupId);

    /**
     * Retrieve name
     *
     * @return string|null
     */
    public function getAttributeGroupName();

    /**
     * Set name
     *
     * @param string $attributeGroupName
     * @return $this
     */
    public function setAttributeGroupName($attributeGroupName);

    /**
     * Retrieve attribute set id
     *
     * @return int|null
     */
    public function getAttributeSetId();

    /**
     * Set attribute set id
     *
     * @param int $attributeSetId
     * @return $this
     */
    public function setAttributeSetId($attributeSetId);

    /**
     * Retrieve sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Retrieve default ID
     *
     * @return int
     */
    public function getDefaultId();

    /**
     * Set default ID
     *
     * @param int $defaultId
     * @return $this
     */
    public function setDefaultId($defaultId);

    /**
     * Retrieve attribute group code
     *
     * @return string
     */
    public function getAttributeGroupCode();

    /**
     * Set attribute group code
     *
     * @param string $attributeGroupCode
     * @return $this
     */
    public function setAttributeGroupCode($attributeGroupCode);

    /**
     * Retrieve scope code
     *
     * @return string
     */
    public function getScopeCode();

    /**
     * Set scope code
     *
     * @param string $scopeCode
     * @return $this
     */
    public function setScopeCode($scopeCode);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Eav\Api\Data\AttributeGroupExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Eav\Api\Data\AttributeGroupExtensionInterface $extensionAttributes
    );
}
