<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\Website;

use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventorySales\Model\GetAssignedStockIdForWebsiteInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetAssignedStockIdForWebsiteTest extends TestCase
{
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var GetAssignedStockIdForWebsiteInterface
     */
    private $getAssignedStockIdForWebsite;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    protected function setUp()
    {
        $this->websiteFactory = Bootstrap::getObjectManager()->get(WebsiteFactory::class);
        $this->getAssignedStockIdForWebsite = Bootstrap::getObjectManager()->get(
            GetAssignedStockIdForWebsiteInterface::class
        );
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
    }

    public function testGetAssignedStocksForNotExistedWebsite()
    {
        self::assertNull($this->getAssignedStockIdForWebsite->execute('not_existed_website_code'));
    }

    /**
     * Creates website inside of test so need to enable db isolation to prevent change db state after test execution
     * @magentoDbIsolation enabled
     */
    public function testGetAssignedStocksForWebsite()
    {
        $websiteCode = 'test_1';

        /** @var Website $website */
        $website = $this->websiteFactory->create();
        $website->setCode($websiteCode);
        // Use website model because we haven't api interfaces for website saving
        $website->save();

        $stockId = $this->getAssignedStockIdForWebsite->execute($websiteCode);
        self::assertEquals($this->defaultStockProvider->getId(), $stockId);
    }
}
