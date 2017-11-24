<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\Website;

use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AssignWebsiteToDefaultStockTest extends TestCase
{
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected function setUp()
    {
        $this->websiteFactory = Bootstrap::getObjectManager()->get(WebsiteFactory::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * Test if Main website is associated to Default stock
     */
    public function testIfWebsiteIsAssignedToDefaultStock()
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();

        $defaultStockId = $this->defaultStockProvider->getId();
        $defaultStock = $this->stockRepository->get($defaultStockId);

        $extensionAttributes = $defaultStock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();
        self::assertContainsOnlyInstancesOf(SalesChannelInterface::class, $salesChannels);
        self::assertCount(1, $salesChannels);

        $salesChannel = reset($salesChannels);
        self::assertEquals($websiteCode, $salesChannel->getCode());
        self::assertEquals(SalesChannelInterface::TYPE_WEBSITE, $salesChannel->getType());
    }

    /**
     * Creates website inside of test so need to enable db isolation to prevent change db state after test execution
     * @magentoDbIsolation enabled
     */
    public function testCreateWebsiteIfSalesChannelsAreEmpty()
    {
        $websiteCode = 'test_1';

        /** @var Website $website */
        $website = $this->websiteFactory->create();
        $website->setCode($websiteCode);
        // Use website model because we haven't api interfaces for website saving
        $website->save();

        $defaultStockId = $this->defaultStockProvider->getId();
        $defaultStock = $this->stockRepository->get($defaultStockId);

        $extensionAttributes = $defaultStock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();
        self::assertContainsOnlyInstancesOf(SalesChannelInterface::class, $salesChannels);

        $salesChannelsWhichBelongToCreatedWebsite = array_filter($salesChannels, function($aSalesChannel) use ($websiteCode) {
            return $aSalesChannel->getCode() === $websiteCode;
        });

        self::assertCount(1, $salesChannelsWhichBelongToCreatedWebsite);

        $aSalesChannelWhichBelongToCreatedWebsite = reset($salesChannelsWhichBelongToCreatedWebsite);
        self::assertEquals($website->getCode(), $aSalesChannelWhichBelongToCreatedWebsite->getCode());
        self::assertEquals(SalesChannelInterface::TYPE_WEBSITE, $aSalesChannelWhichBelongToCreatedWebsite->getType());
    }
}
