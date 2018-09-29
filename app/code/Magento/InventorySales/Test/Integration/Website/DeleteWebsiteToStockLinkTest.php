<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\Website;

use Magento\Framework\Registry;
use Magento\InventorySalesApi\Model\GetAssignedStockIdForWebsiteInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DeleteWebsiteToStockLinkTest extends TestCase
{
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var GetAssignedStockIdForWebsiteInterface
     */
    private $getAssignedStockIdForWebsite;

    protected function setUp()
    {
        $this->websiteFactory = Bootstrap::getObjectManager()->get(WebsiteFactory::class);
        $this->getAssignedStockIdForWebsite = Bootstrap::getObjectManager()->get(
            GetAssignedStockIdForWebsiteInterface::class
        );
    }

    public function testDeleteWebsiteToStockLink()
    {
        $websiteCode = 'test_1';

        /** @var Website $website */
        $website = $this->websiteFactory->create();
        $website->setCode($websiteCode);
        // Use website model because we haven't api interfaces for website saving/deleting
        $website->save();
        $this->deleteWebsite($website);

        $stockId = $this->getAssignedStockIdForWebsite->execute($websiteCode);
        self::assertNull($stockId);
    }

    /**
     * @param Website $website
     * @return void
     */
    private function deleteWebsite(Website $website)
    {
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $website->delete();
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
