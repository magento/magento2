<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder\Factory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Framework\Api\Filter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product;

class FactoryTest extends \PHPUnit\Framework\TestCase
{

    private $productResourceMock;

    private $eavConfigMock;

    private $eavAttrConditionBuilderMock;

    private $nativeAttrConditionBuilderMock;

    private $conditionBuilderFactory;

    protected function setUp()
    {
        $this->productResourceMock = $this->getMockBuilder(ProductResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityTable'])
            ->getMock();

        $this->eavConfigMock = $this->getMockBuilder(EavConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();

        $this->eavAttrConditionBuilderMock = $this->getMockBuilder(CustomConditionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->nativeAttrConditionBuilderMock = $this->getMockBuilder(CustomConditionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->conditionBuilderFactory = $objectManagerHelper->getObject(
            Factory::class,
            [
                'eavConfig' => $this->eavConfigMock,
                'productResource' => $this->productResourceMock,
                'eavAttributeConditionBuilder' => $this->eavAttrConditionBuilderMock,
                'nativeAttributeConditionBuilder' => $this->nativeAttrConditionBuilderMock,
            ]
        );
    }

    public function testNativeAttrConditionBuilder()
    {
        $fieldName = 'super_field';
        $attributeTable = 'my-table';
        $productResourceTable = 'my-table';

        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getField'])
            ->getMock();

        $filterMock
            ->method('getField')
            ->willReturn($fieldName);

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBackendTable'])
            ->getMock();

        $this->eavConfigMock
            ->method('getAttribute')
            ->with(Product::ENTITY, $fieldName)
            ->willReturn($attributeMock);

        $attributeMock
            ->method('getBackendTable')
            ->willReturn($attributeTable);

        $this->productResourceMock
            ->method('getEntityTable')
            ->willReturn($productResourceTable);

        $this->assertEquals(
            $this->nativeAttrConditionBuilderMock,
            $this->conditionBuilderFactory->createByFilter($filterMock)
        );
    }

    public function testEavAttrConditionBuilder()
    {
        $fieldName = 'super_field';
        $attributeTable = 'my-table';
        $productResourceTable = 'not-my-table';

        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getField'])
            ->getMock();

        $filterMock
            ->method('getField')
            ->willReturn($fieldName);

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBackendTable'])
            ->getMock();

        $this->eavConfigMock
            ->method('getAttribute')
            ->with(Product::ENTITY, $fieldName)
            ->willReturn($attributeMock);

        $attributeMock
            ->method('getBackendTable')
            ->willReturn($attributeTable);

        $this->productResourceMock
            ->method('getEntityTable')
            ->willReturn($productResourceTable);

        $this->assertEquals(
            $this->eavAttrConditionBuilderMock,
            $this->conditionBuilderFactory->createByFilter($filterMock)
        );
    }
}
