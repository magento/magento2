<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
