<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration;

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
        $this->assertNull($this->getAssignedStockIdForWebsite->execute('not_existed_website_code'));
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites.php
     *
     * Creates website inside of test so need to enable db isolation to prevent change db state after test execution
     * @magentoDbIsolation enabled
     */
    public function testGetAssignedStocksForWebsite()
    {
        // Use website model because we haven't api interfaces for website saving

        /** @var Website $website */
        $website = $this->websiteFactory->create();
        $websiteCode = 'test_1';
        $website->load($websiteCode);
        $website->setCode($websiteCode);
        $website->save();

        $stockId = $this->getAssignedStockIdForWebsite->execute($websiteCode);
        $this->assertEquals($this->defaultStockProvider->getId(), $stockId);
    }
}
