<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

class Management implements \Magento\Catalog\Api\ProductAttributeManagementInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeManagementInterface
     */
    protected $eavAttributeManagement;

    /**
     * @param \Magento\Eav\Api\AttributeManagementInterface $eavAttributeManagement
     */
    public function __construct(
        \Magento\Eav\Api\AttributeManagementInterface $eavAttributeManagement
    ) {
        $this->eavAttributeManagement = $eavAttributeManagement;
    }

    /**
     * {@inheritdoc}
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
     */
    public function unassign($attributeSetId, $attributeCode)
    {
        return $this->eavAttributeManagement->unassign($attributeSetId, $attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($attributeSetId)
    {
        return $this->eavAttributeManagement->getAttributes(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSetId
        );
    }
}
