<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\InventorySalesApi;

use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AdaptStockResolverToAdminWebsiteTest extends TestCase
{
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    protected function setUp()
    {
        $this->stockResolver = Bootstrap::getObjectManager()->get(StockResolverInterface::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
    }

    public function testAdaptStockResolverToAdminWebsite()
    {
        $defaultStockId = $this->defaultStockProvider->getId();
        $currentStock = $this->stockResolver->execute(
            SalesChannelInterface::TYPE_WEBSITE,
            WebsiteInterface::ADMIN_CODE
        );

        self::assertEquals($defaultStockId, $currentStock->getStockId());
    }
}
