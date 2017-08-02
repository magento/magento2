<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

/**
 * Class \Magento\Catalog\Model\Product\Attribute\Management
 *
 * @since 2.0.0
 */
class Management implements \Magento\Catalog\Api\ProductAttributeManagementInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeManagementInterface
     * @since 2.0.0
     */
    protected $eavAttributeManagement;

    /**
     * @param \Magento\Eav\Api\AttributeManagementInterface $eavAttributeManagement
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Eav\Api\AttributeManagementInterface $eavAttributeManagement
    ) {
        $this->eavAttributeManagement = $eavAttributeManagement;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function assign($attributeSetId, $attributeGroupId, $attributeCode, $sortOrder)
    {
        return $this->eavAttributeManagement->assign(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSetId,
            $attributeGroupId,
            $attributeCode,
            $sortOrder
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function unassign($attributeSetId, $attributeCode)
    {
        return $this->eavAttributeManagement->unassign($attributeSetId, $attributeCode);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAttributes($attributeSetId)
    {
        return $this->eavAttributeManagement->getAttributes(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSetId
        );
    }
}
