<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Test\Unit\Model\Rule\Condition;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogWidget\Model\Rule\Condition\Product
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $eavConfig = $this->getMock(\Magento\Eav\Model\Config::class, [], [], '', false);
        $this->attributeMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [],
            [],
            '',
            false
        );
        $eavConfig->expects($this->once())->method('getAttribute')->willReturn($this->attributeMock);
        $ruleMock = $this->getMock(\Magento\SalesRule\Model\Rule::class, [], [], '', false);
        $storeManager = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeMock = $this->getMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $productResource = $this->getMock(\Magento\Catalog\Model\ResourceModel\Product::class, [], [], '', false);
        $productResource->expects($this->once())->method('loadAllAttributes')->willReturnSelf();
        $productResource->expects($this->once())->method('getAttributesByCode')->willReturn([]);
        $this->model = $objectManagerHelper->getObject(
            \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
            [
                'config' => $eavConfig,
                'storeManager' => $storeManager,
                'productResource' => $productResource,
                'data' => [
                    'rule' => $ruleMock,
                    'id' => 1
                ]
            ]
        );
    }

    public function testAddToCollection()
    {
        $collectionMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [],
            [],
            '',
            false
        );
        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $collectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('join')->willReturnSelf();
        $this->attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');
        $this->attributeMock->expects($this->once())->method('isStatic')->willReturn(false);
        $this->attributeMock->expects($this->once())->method('getBackend')->willReturn(true);
        $this->attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(true);
        $this->attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(true);
        $this->attributeMock->expects($this->once())->method('getBackendType')->willReturn('multiselect');
        $this->model->addToCollection($collectionMock);
    }
}
