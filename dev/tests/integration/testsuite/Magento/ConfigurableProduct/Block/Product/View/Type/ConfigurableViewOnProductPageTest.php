<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class checks configurable product view with out of stock children
 *
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableViewOnProductPageTest extends TestCase
{
    private const STOCK_DISPLAY_TEMPLATE = 'Magento_Catalog::product/view/type/default.phtml';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var LayoutInterface */
    private $layout;

    /** @var Configurable */
    private $block;

    /** @var SerializerInterface */
    private $json;

    /** @var ProductResource */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Configurable::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
    }

    /**
     * @dataProvider oneChildNotVisibleDataProvider
     * @magentoDbIsolation disabled
     *
     * @param string $sku
     * @param array $data
     * @param array $expectedData
     * @return void
     */
    public function testOneChildNotVisible(string $sku, array $data, array $expectedData): void
    {
        $configurableProduct = $this->prepareProductToTest($sku, $data);
        $result = $this->renderStockBlock($configurableProduct);
        $this->performAsserts($result, $expectedData);
    }

    /**
     * @return array
     */
    public function oneChildNotVisibleDataProvider(): array
    {
        return [
            'one_child_out_of_stock' => [
                'sku' => 'simple_10',
                'data' => [
                    'stock_data' => [
                        'use_config_manage_stock' => 1,
                        'is_in_stock' => StockStatusInterface::STATUS_OUT_OF_STOCK,
                    ],
                ],
                'expected_data' => [
                    'stock_status' => 'In stock',
                    'options' => [
                        [
                            'label' => 'Option 2',
                            'product' => 'simple_20',
                        ],
                    ],
                ],
            ],
            'one_child_disabled' => [
                'sku' => 'simple_10',
                'data' => [
                    'status' => Status::STATUS_DISABLED,
                    'stock_data' => [
                        'use_config_manage_stock' => 1,
                        'is_in_stock' => StockStatusInterface::STATUS_IN_STOCK,
                    ],
                ],
                'expected_data' => [
                    'stock_status' => 'In stock',
                    'options' => [
                        [
                            'label' => 'Option 2',
                            'product' => 'simple_20',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 1
     *
     * @dataProvider oneChildNotVisibleDataProviderWithEnabledConfig
     *
     * @param string $sku
     * @param array $data
     * @param array $expectedData
     * @return void
     */
    public function testOneChildNotVisibleWithEnabledShowOutOfStockProducts(
        string $sku,
        array $data,
        array $expectedData
    ): void {
        $configurableProduct = $this->prepareProductToTest($sku, $data);
        $result = $this->renderStockBlock($configurableProduct);
        $this->performAsserts($result, $expectedData);
    }

    /**
     * @return array
     */
    public function oneChildNotVisibleDataProviderWithEnabledConfig(): array
    {
        return [
            'one_child_out_of_stock' => [
                'sku' => 'simple_10',
                'data' => [
                    'stock_data' => [
                        'use_config_manage_stock' => 1,
                        'is_in_stock' => StockStatusInterface::STATUS_OUT_OF_STOCK,
                    ],
                ],
                'expected_data' => [
                    'stock_status' => 'In stock',
                    'options' => [
                        [
                            'label' => 'Option 2',
                            'product' => 'simple_20'
                        ],
                        [
                            'label' => 'Option 1',
                            'product' => 'simple_10',
                        ],
                    ],
                ],
            ],
            'one_child_disabled' => [
                'sku' => 'simple_10',
                'data' => [
                    'status' => Status::STATUS_DISABLED,
                    'stock_data' => [
                        'use_config_manage_stock' => 1,
                        'is_in_stock' => StockStatusInterface::STATUS_IN_STOCK,
                    ],
                ],
                'expected_data' => [
                    'stock_status' => 'In stock',
                    'options' => [
                        [
                            'label' => 'Option 2',
                            'product' => 'simple_20',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Update product with data
     *
     * @param array $sku
     * @param array $data
     * @return void
     */
    private function updateProduct(string $sku, array $data): void
    {
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $currentStore = $storeManager->getStore();
        try {
            $storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
            $product = $this->productRepository->get($sku);
            $product->addData($data);
            $this->productRepository->save($product);
        } finally {
            $storeManager->setCurrentStore($currentStore);
        }
    }

    /**
     * Check attribute options
     *
     * @param array $actualAttributeDataData
     * @param array $actualOptionData
     * @param array $expectedData
     * @return void
     */
    private function assertConfig(array $actualAttributeDataData, array $actualOptionData, array $expectedData): void
    {
        $this->assertCount(count($expectedData), $actualOptionData, 'Redundant options were loaded');
        foreach ($expectedData as $expectedOption) {
            $expectedId = $this->productResource->getIdBySku($expectedOption['product']);
            $itemToCheck = $actualOptionData[$expectedId] ?? null;
            $this->assertNotNull($itemToCheck);
            foreach ($actualAttributeDataData['options'] as $actualAttributeDataItem) {
                if ($actualAttributeDataItem['id'] === reset($itemToCheck)) {
                    $this->assertEquals($expectedOption['label'], $actualAttributeDataItem['label']);
                }
            }
        }
    }

    /**
     * Render stock block
     *
     * @param ProductInterface $configurableProduct
     * @return string
     */
    private function renderStockBlock(ProductInterface $configurableProduct): string
    {
        $this->block->setProduct($configurableProduct);
        $this->block->setTemplate(self::STOCK_DISPLAY_TEMPLATE);

        return $this->block->toHtml();
    }

    /**
     * Perform test asserts
     *
     * @param string $result
     * @param array $expectedData
     * @return void
     */
    private function performAsserts(string $result, array $expectedData): void
    {
        $this->assertEquals((string)__($expectedData['stock_status']), trim(strip_tags($result)));
        $config = $this->json->unserialize($this->block->getJsonConfig());
        $this->assertConfig(reset($config['attributes']), $config['index'], $expectedData['options']);
    }

    /**
     * Prepare configurable product with children to test
     *
     * @param string $sku
     * @param array $data
     * @return ProductInterface
     */
    private function prepareProductToTest(string $sku, array $data): ProductInterface
    {
        $this->updateProduct($sku, $data);
        $configurableProduct = $this->productRepository->get('configurable', false, null, true);

        return $configurableProduct;
    }
}
