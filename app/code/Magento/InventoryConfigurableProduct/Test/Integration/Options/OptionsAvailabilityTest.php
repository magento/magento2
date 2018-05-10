<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Options;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableView;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 */
class OptionsAvailabilityTest extends TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConfigurableView
     */
    private $configurableView;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->configurableView = Bootstrap::getObjectManager()->get(ConfigurableView::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->serializer = Bootstrap::getObjectManager()->get(SerializerInterface::class);
    }

    /**
     * @codingStandardsIgnoreStart
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @codingStandardsIgnoreEnd
     * @dataProvider getSalableOptionsDataProvider
     * @param string $storeCode
     * @param int $expected
     * @return void
     *
     * @magentoDbIsolation disabled
     */
    public function testGetSalableOptions(string $storeCode, int $expected)
    {
        $this->storeManager->setCurrentStore($storeCode);

        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $this->configurableView->setProduct($configurableProduct);
        $result = $this->serializer->unserialize($this->configurableView->getJsonConfig());
        $attributes = reset($result['attributes']);
        $actual = count($attributes['options'] ?? []);

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @return array
     */
    public function getSalableOptionsDataProvider()
    {
        return [
            [
                'store_for_eu_website',
                0
            ],
            [
                'store_for_us_website',
                2
            ],
        ];
    }
}
