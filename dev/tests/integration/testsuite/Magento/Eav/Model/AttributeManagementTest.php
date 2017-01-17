<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

class AttributeManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Api\AttributeManagementInterface
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\Eav\Api\AttributeManagementInterface::class);
    }

    /**
     * Verify that collection in service used correctly
     */
    public function testGetList()
    {
        $productAttributeSetId = $this->getAttributeSetId(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE
        );
        $productAttributes = $this->model->getAttributes(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $productAttributeSetId
        );
        // Verify that result contains only product attributes
        $this->verifyAttributeSetIds($productAttributes, $productAttributeSetId);

        $categoryAttributeSetId = $this->getAttributeSetId(
            \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE
        );
        $categoryAttributes = $this->model->getAttributes(
            \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $categoryAttributeSetId
        );
        // Verify that result contains only category attributes
        $this->verifyAttributeSetIds($categoryAttributes, $categoryAttributeSetId);
    }

    /**
     * @param string $entityTypeCode
     * @return int
     */
    private function getAttributeSetId($entityTypeCode)
    {
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = $this->objectManager->create(\Magento\Eav\Model\Config::class);
        return $eavConfig->getEntityType($entityTypeCode)->getDefaultAttributeSetId();
    }

    /**
     * @param array $items
     * @param string $attributeSetId
     * @return void
     */
    private function verifyAttributeSetIds(array $items, $attributeSetId)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $item */
        foreach ($items as $item) {
            $this->assertEquals($attributeSetId, $item->getAttributeSetId());
        }
    }
}
