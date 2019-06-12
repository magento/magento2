<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory as StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\Exception\InputException;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Class StockRegistryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteria;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StockItemCriteriaInterfaceFactory::class
     */
    private $criteriaFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\CatalogInventory\Api\Data\StockItemInterface::class
     */
    private $stockRegistryProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    protected function setUp()
    {
        $this->criteria = $this->getMockBuilder(StockItemCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $criteriaFactory = $this->getMockBuilder(StockItemCriteriaInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockRegistryProvider = $this->createMock(StockRegistryProviderInterface::class);
        $this->stockConfiguration = $this->createMock(StockConfigurationInterface::class);
        $this->productFactory = $this->createMock(ProductFactory::class);

        $this->criteriaFactory = $criteriaFactory;

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\CatalogInventory\Model\StockRegistry::class,
            [
                'criteriaFactory' => $criteriaFactory,
                'stockRegistryProvider' => $this->stockRegistryProvider,
                'stockConfiguration' => $this->stockConfiguration,
                'productFactory' => $this->productFactory
            ]
        );
    }

    public function testGetLowStockItems()
    {
        $this->criteriaFactory->expects($this->once())->method('create')->willReturn($this->criteria);

        $this->criteria->expects($this->once())->method('setLimit')->with(1, 0);
        $this->criteria->expects($this->once())->method('setScopeFilter')->with(1);
        $this->criteria->expects($this->once())->method('setQtyFilter')->with('<=');
        $this->criteria->expects($this->once())->method('addField')->with('qty');
        $this->model->getLowStockItems(1, 100);
    }

    public function testUsesCorrectScope()
    {
        $expectedId = 123123;
        $expectedSku = "EXPECTED-SKU";
        $expectedCustomSource = 5;
        $expectedDefaultSource = 0;
        $expectedQty = 525;

        $product = $this->createMock(Product::class);
        $this->productFactory->method("create")->willReturn($product);
        $product->method("getIdBySku")->willReturn($expectedId);

        $this->stockConfiguration->method("getDefaultScopeId")->willReturn($expectedDefaultSource);

        $stockItem = $this->createMock(StockItemInterface::class);
        $stockStatus = $this->createMock(StockStatusInterface::class);
        $stockStatus->method("getStockStatus")->willReturn($expectedQty);

        $this->stockRegistryProvider
            ->expects($this->exactly(4))
            ->method("getStockItem")
            ->withConsecutive(
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedCustomSource)
                ],
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedDefaultSource)
                ],
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedCustomSource)
                ],
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedDefaultSource)
                ]
            )
            ->willReturn($stockItem);

        $this->stockRegistryProvider
            ->expects($this->exactly(6))
            ->method("getStockStatus")
            ->withConsecutive(
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedCustomSource)
                ],
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedDefaultSource)
                ],
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedCustomSource)
                ],
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedDefaultSource)
                ],
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedCustomSource)
                ],
                [
                    $this->logicalAnd($this->isType(IsType::TYPE_INT), $this->equalTo($expectedId)),
                    $this->equalTo($expectedDefaultSource)
                ]
            )
            ->willReturn($stockStatus);

        $this->model->getStockItem($expectedId, $expectedCustomSource);
        $this->model->getStockItem($expectedId, null);

        $this->model->getStockItemBySku($expectedSku, $expectedCustomSource);
        $this->model->getStockItemBySku($expectedSku, null);

        $this->model->getStockStatus($expectedId, $expectedCustomSource);
        $this->model->getStockStatus($expectedId, null);

        $this->model->getStockStatusBySku($expectedId, $expectedCustomSource);
        $this->model->getStockStatusBySku($expectedId, null);

        $this->assertEquals($this->model->getProductStockStatus($expectedId, $expectedCustomSource), $expectedQty);
        $this->assertEquals($this->model->getProductStockStatus($expectedId, null), $expectedQty);

        $this->expectException(InputException::class);
        $this->model->getStockItem($expectedId, "COMPLETELY INVALID SCOPE");
    }
}
