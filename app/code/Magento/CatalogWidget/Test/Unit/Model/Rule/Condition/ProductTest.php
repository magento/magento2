<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogWidget\Test\Unit\Model\Rule\Condition;

use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogWidget\Model\Rule\Condition\Product as ProductWidget;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    /**
     * @var ProductWidget
     */
    private $model;

    /**
     * @var MockObject
     */
    private $attributeMock;

    /**
     * @var Product|MockObject
     */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $eavConfig = $this->createMock(Config::class);
        $this->attributeMock = $this->createMock(Attribute::class);
        $eavConfig->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);
        $ruleMock = $this->createMock(Rule::class);
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($storeMock);
        $this->productResource = $this->createMock(Product::class);
        $this->productResource->expects($this->once())->method('loadAllAttributes')->willReturnSelf();
        $this->productResource->expects($this->once())->method('getAttributesByCode')->willReturn([]);
        $productCategoryList = $this->getMockBuilder(ProductCategoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            ProductWidget::class,
            [
                'config' => $eavConfig,
                'storeManager' => $storeManager,
                'productResource' => $this->productResource,
                'productCategoryList' => $productCategoryList,
                'data' => [
                    'rule' => $ruleMock,
                    'id' => 1
                ]
            ]
        );
    }

    /**
     * Test addToCollection method.
     *
     * @return void
     */
    public function testAddToCollection()
    {
        $collectionMock = $this->createMock(Collection::class);
        $selectMock = $this->createMock(Select::class);
        $collectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('join')->willReturnSelf();
        $this->attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');
        $this->attributeMock->expects($this->once())->method('isStatic')->willReturn(false);
        $this->attributeMock->expects($this->once())->method('getBackend')->willReturn(true);
        $this->attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(true);
        $this->attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(true);
        $this->attributeMock->expects($this->once())->method('getBackendType')->willReturn('multiselect');

        $entityMock = $this->createMock(AbstractEntity::class);
        $entityMock->expects($this->once())->method('getLinkField')->willReturn('entitiy_id');
        $this->attributeMock->expects($this->once())->method('getEntity')->willReturn($entityMock);
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->productResource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);

        $this->model->addToCollection($collectionMock);
    }

    /**
     * Test getMappedSqlField method.
     *
     * @return void
     */
    public function testGetMappedSqlFieldSku()
    {
        $this->model->setAttribute('sku');
        $this->assertEquals('e.sku', $this->model->getMappedSqlField());
        $this->model->setAttribute('attribute_set_id');
        $this->assertEquals('e.attribute_set_id', $this->model->getMappedSqlField());
    }
}
