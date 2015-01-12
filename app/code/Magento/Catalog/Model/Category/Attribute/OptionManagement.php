<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Attribute;

/**
 * Product options management class
 */
class OptionManagement implements \Magento\Catalog\Api\CategoryAttributeOptionManagementInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected $eavOptionManagement;

    /**
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $eavOptionManagement
     */
    public function __construct(
        \Magento\Eav\Api\AttributeOptionManagementInterface $eavOptionManagement
    ) {
        $this->eavOptionManagement = $eavOptionManagement;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getItems($attributeCode)
    {
        return $this->eavOptionManagement->getItems(
            \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
    }
}
