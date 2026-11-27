<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Helper\Product;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ProductListTest extends TestCase
{
    /**
     * @var ProductList
     */
    private $helper;

    protected function setUp(): void
    {
        $this->helper = Bootstrap::getObjectManager()->create(ProductList::class);
    }

    #[
        Config('general/single_store_mode/enabled', 1),
        Config('general/single_store_mode/enabled', 1, ScopeInterface::SCOPE_STORE, 'default'),
        Config('catalog/frontend/grid_per_page_values', '12,24,36'),
        Config('catalog/frontend/grid_per_page', 24),
        Config('catalog/frontend/list_allow_all', 1),
        Config('catalog/frontend/grid_per_page_values', '6,9', ScopeInterface::SCOPE_STORE, 'default'),
        Config('catalog/frontend/grid_per_page', 9, ScopeInterface::SCOPE_STORE, 'default'),
        Config('catalog/frontend/list_allow_all', 0, ScopeInterface::SCOPE_STORE, 'default'),
    ]
    public function testSingleStoreModeIgnoresStoreOverrides(): void
    {
        $this->assertSame(
            [12, 24, 36, 'all'],
            array_keys($this->helper->getAvailableLimit('grid'))
        );
        $this->assertSame(24, $this->helper->getDefaultLimitPerPageValue('grid'));
    }

    #[
        Config('general/single_store_mode/enabled', 0),
        Config('general/single_store_mode/enabled', 0, ScopeInterface::SCOPE_STORE, 'default'),
        Config('catalog/frontend/grid_per_page_values', '12,24,36'),
        Config('catalog/frontend/grid_per_page', 24),
        Config('catalog/frontend/list_allow_all', 1),
        Config('catalog/frontend/grid_per_page_values', '6,9', ScopeInterface::SCOPE_STORE, 'default'),
        Config('catalog/frontend/grid_per_page', 9, ScopeInterface::SCOPE_STORE, 'default'),
        Config('catalog/frontend/list_allow_all', 0, ScopeInterface::SCOPE_STORE, 'default'),
    ]
    public function testMultiStoreModeKeepsStoreOverrides(): void
    {
        $this->assertSame([6, 9], array_keys($this->helper->getAvailableLimit('grid')));
        $this->assertSame(9, $this->helper->getDefaultLimitPerPageValue('grid'));
    }
}
