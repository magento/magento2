<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Plugin;

use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\CatalogSearch\Model\Indexer\Plugin\StockedProductsFilterPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\CatalogSearch\Model\Indexer\Plugin\StockedProductsFilterPlugin class.
 *
 * This plugin reverts changes introduced in commit 9ab466d8569ea556cb01393989579c3aac53d9a3 which break extensions
 * relying on stocks. Plugin location is changed for consistency purposes.
 */
class StockedProductsFilterPluginTest extends TestCase
{
    /**
     * @var StockConfigurationInterface|MockObject
     */
    private $stockConfigurationMock;

    /**
     * @var StockStatusRepositoryInterface|MockObject
     */
    private $stockStatusRepositoryMock;

    /**
     * @var StockStatusCriteriaInterfaceFactory|MockObject
     */
    private $stockStatusCriteriaFactoryMock;

    /**
     * @var StockedProductsFilterPlugin
     */
    private $plugin;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStatusRepositoryMock = $this->getMockBuilder(StockStatusRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStatusCriteriaFactoryMock = $this->getMockBuilder(StockStatusCriteriaInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new StockedProductsFilterPlugin(
            $this->stockConfigurationMock,
            $this->stockStatusRepositoryMock,
            $this->stockStatusCriteriaFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testBeforePrepareProductIndex(): void
    {
        /** @var DataProvider|MockObject $dataProviderMock */
        $dataProviderMock = $this->getMockBuilder(DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $indexData = [
            1 => [],
            2 => [],
        ];
        $productData = [];
        $storeId = 1;

        $this->stockConfigurationMock
            ->expects($this->once())
            ->method('isShowOutOfStock')
            ->willReturn(false);

        $stockStatusCriteriaMock = $this->getMockBuilder(StockStatusCriteriaInterface::class)
            ->getMock();
        $stockStatusCriteriaMock
            ->expects($this->once())
            ->method('setProductsFilter')
            ->willReturn(true);
        $this->stockStatusCriteriaFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($stockStatusCriteriaMock);

        $stockStatusMock = $this->getMockBuilder(StockStatusInterface::class)
            ->getMock();
        $stockStatusMock->expects($this->atLeastOnce())
            ->method('getStockStatus')
            ->willReturnOnConsecutiveCalls(Stock::STOCK_IN_STOCK, Stock::STOCK_OUT_OF_STOCK);
        $stockStatusCollectionMock = $this->getMockBuilder(StockStatusCollectionInterface::class)
            ->getMock();
        $stockStatusCollectionMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([1 => $stockStatusMock, 2 => $stockStatusMock]);
        $this->stockStatusRepositoryMock
            ->expects($this->once())
            ->method('getList')
            ->willReturn($stockStatusCollectionMock);

        list($indexData, $productData, $storeId) = $this->plugin->beforePrepareProductIndex(
            $dataProviderMock,
            $indexData,
            $productData,
            $storeId
        );

        $this->assertEquals([1], array_keys($indexData));
    }
}
