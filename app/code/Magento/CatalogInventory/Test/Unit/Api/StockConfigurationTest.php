<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Api;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Helper\Minsaleqty;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StockConfigurationTest extends TestCase
{
    /** @var StockConfigurationInterface */
    protected $stockConfiguration;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $config;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var Minsaleqty|MockObject
     */
    protected $minsaleqtyHelper;

    protected function setUp(): void
    {
        $this->config = $this->getMockForAbstractClass(
            ConfigInterface::class,
            [],
            '',
            false
        );
        $this->scopeConfig = $this->getMockForAbstractClass(
            ScopeConfigInterface::class,
            ['isSetFlag'],
            '',
            false
        );

        $this->minsaleqtyHelper = $this->createMock(Minsaleqty::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->stockConfiguration = $this->objectManagerHelper->getObject(
            Configuration::class,
            [
                'config' => $this->config,
                'scopeConfig' => $this->scopeConfig,
                'minsaleqtyHelper' => $this->minsaleqtyHelper
            ]
        );
    }

    public function testGetConfigItemOptions()
    {
        $configOptions = [
            'min_qty',
            'backorders',
            'min_sale_qty',
            'max_sale_qty',
            'notify_stock_qty',
            'manage_stock',
            'enable_qty_increments',
            'qty_increments',
            'is_decimal_divided',
        ];
        $this->assertSame($configOptions, $this->stockConfiguration->getConfigItemOptions());
    }

    public function testIsShowOutOfStock()
    {
        $store = 0;
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
                ScopeInterface::SCOPE_STORE,
                $store
            )
            ->willReturn(true);
        $this->assertTrue($this->stockConfiguration->isShowOutOfStock());
    }

    public function testIsAutoReturnEnabled()
    {
        $store = 0;
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Configuration::XML_PATH_ITEM_AUTO_RETURN,
                ScopeInterface::SCOPE_STORE,
                $store
            )
            ->willReturn(true);
        $this->assertTrue($this->stockConfiguration->isAutoReturnEnabled());
    }

    public function testIsDisplayProductStockStatus()
    {
        $store = 0;
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Configuration::XML_PATH_DISPLAY_PRODUCT_STOCK_STATUS,
                ScopeInterface::SCOPE_STORE,
                $store
            )
            ->willReturn(true);
        $this->assertTrue($this->stockConfiguration->isDisplayProductStockStatus());
    }
}
