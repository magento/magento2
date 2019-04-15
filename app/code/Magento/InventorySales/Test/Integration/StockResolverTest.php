<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class StockResolverTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var StockResolverInterface
     */
    private $stockResolverInterface;

    /**
     * Create objects.
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->stockRepository = $this->objectManager->get(StockRepositoryInterface::class);
        $this->salesChannelFactory = $this->objectManager->get(SalesChannelInterfaceFactory::class);
        $this->stockResolverInterface = $this->objectManager->get(StockResolverInterface::class);
    }

    /**
     * Resolve stock and check data.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     *
     * @magentoDbIsolation disabled
     */
    public function testExecute()
    {
        /** @var StockInterface $stock */
        $stock = $this->stockResolverInterface->execute(SalesChannelInterface::TYPE_WEBSITE, 'eu_website');
        $this->assertEquals(10, $stock->getStockId());
        $this->assertEquals('EU-stock', $stock->getName());
        $salesChannels = $stock->getExtensionAttributes()->getSalesChannels();
        $this->assertEquals(1, count($salesChannels));
        /** @var SalesChannelInterface $salesChannel */
        $salesChannel = $salesChannels[0];
        $this->assertEquals('website', $salesChannel->getType());
        $this->assertEquals('eu_website', $salesChannel->getCode());
    }

    /**
     * Resolve stock and check data after sales channels was changed.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     *
     * @magentoDbIsolation disabled
     */
    public function testExecuteAfterChangeSalesChannelsTest()
    {
        $stockId = 20;
        /** @var StockInterface $stock */
        $stock = $this->stockResolverInterface->execute(SalesChannelInterface::TYPE_WEBSITE, 'us_website');
        $this->assertEquals($stockId, $stock->getStockId());
        $this->assertEquals('US-stock', $stock->getName());
        $salesChannels = $stock->getExtensionAttributes()->getSalesChannels();
        $this->assertEquals(1, count($salesChannels));
        /** @var SalesChannelInterface $salesChannel */
        $salesChannel = $salesChannels[0];
        $this->assertEquals('website', $salesChannel->getType());
        $this->assertEquals('us_website', $salesChannel->getCode());

        /** @var StockInterface $stock */
        $stock = $this->stockRepository->get($stockId);
        $salesChannel = $this->salesChannelFactory->create();
        $salesChannel->setCode('global_website');
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
        $stock->getExtensionAttributes()->setSalesChannels([$salesChannel]);
        $this->stockRepository->save($stock);

        $stock = $this->stockResolverInterface->execute(SalesChannelInterface::TYPE_WEBSITE, 'global_website');
        $this->assertEquals($stockId, $stock->getStockId());
        $this->assertEquals('US-stock', $stock->getName());
        $salesChannels = $stock->getExtensionAttributes()->getSalesChannels();
        $this->assertEquals(1, count($salesChannels));
        /** @var SalesChannelInterface $salesChannel */
        $salesChannel = $salesChannels[0];
        $this->assertEquals('website', $salesChannel->getType());
        $this->assertEquals('global_website', $salesChannel->getCode());
    }

    /**
     * Get error when try resolve stock.
     */
    public function testExecuteWithError()
    {
        try {
            $this->stockResolverInterface->execute(SalesChannelInterface::TYPE_WEBSITE, 'us_website');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals('No linked stock found', $e->getMessage());
        }
    }
}
