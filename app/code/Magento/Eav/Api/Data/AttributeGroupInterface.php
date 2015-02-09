<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

interface AttributeGroupInterface
{
    const GROUP_ID = 'attribute_group_id';

    const GROUP_NAME = 'attribute_group_name';

    const ATTRIBUTE_SET_ID = 'attribute_set_id';

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
}
