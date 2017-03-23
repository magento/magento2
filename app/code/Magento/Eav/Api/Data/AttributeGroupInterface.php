<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface AttributeGroupInterface
 * @api
 */
interface AttributeGroupInterface extends ExtensibleDataInterface
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
