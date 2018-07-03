<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogWidget\Test\Unit\Model\Rule\Condition;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \PHPUnit\Framework\TestCase
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

        $eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->attributeMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $eavConfig->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);
        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($storeMock);
        $productResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productResource->expects($this->once())->method('loadAllAttributes')->willReturnSelf();
        $productResource->expects($this->once())->method('getAttributesByCode')->willReturn([]);
        $productCategoryList = $this->getMockBuilder(\Magento\Catalog\Model\ProductCategoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
            [
                'config' => $eavConfig,
                'storeManager' => $storeManager,
                'productResource' => $productResource,
                'productCategoryList' => $productCategoryList,
                'data' => [
                    'rule' => $ruleMock,
                    'id' => 1
                ]
            ]
        );
    }

    public function testAddToCollection()
    {
        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
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

    public function testGetMappedSqlFieldSku()
    {
        $this->model->setAttribute('sku');
        $this->assertEquals('e.sku', $this->model->getMappedSqlField());
    }
}
