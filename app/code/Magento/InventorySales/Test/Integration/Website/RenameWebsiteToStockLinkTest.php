<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\Website;

use Magento\InventorySalesApi\Model\GetAssignedStockIdForWebsiteInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\WebsiteFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RenameWebsiteToStockLinkTest extends TestCase
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
     * @var WebsiteResource
     */
    private $websiteResource;

    protected function setUp()
    {
        $this->websiteFactory = Bootstrap::getObjectManager()->get(WebsiteFactory::class);
        $this->getAssignedStockIdForWebsite = Bootstrap::getObjectManager()->get(
            GetAssignedStockIdForWebsiteInterface::class
        );
        $this->websiteResource = Bootstrap::getObjectManager()->get(WebsiteResource::class);
    }

    /**
     * @throws \Exception
     * @magentoDbIsolation enabled
     */
    public function testRenameWebsiteToStockLink()
    {
        $oldWebsiteCode = 'old_website_code';
        $newWebsiteCode = 'new_website_code';

        /** @var Website $website */
        $website = $this->websiteFactory->create();
        $website->setCode($oldWebsiteCode);
        $this->websiteResource->save($website);
        $websiteId = $website->getId();

        $website = $this->websiteFactory->create();
        $this->websiteResource->load($website, $websiteId);
        $website->setCode($newWebsiteCode);
        $this->websiteResource->save($website);

        self::assertNull(
            $this->getAssignedStockIdForWebsite->execute($oldWebsiteCode),
            'Old website link was not removed'
        );

        self::assertNotNull(
            $this->getAssignedStockIdForWebsite->execute($newWebsiteCode),
            'Website link was not renamed'
        );
    }
}
