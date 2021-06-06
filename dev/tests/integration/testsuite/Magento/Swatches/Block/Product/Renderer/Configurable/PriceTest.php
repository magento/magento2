<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Block\Product\Renderer\Configurable;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Swatches\Block\Product\Renderer\Configurable;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check configurable product price
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class PriceTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Page */
    private $page;

    /** @var SerializerInterface */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->page = $this->objectManager->get(Page::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->json = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('product');

        parent::tearDown();
    }

    /**
     * @dataProvider childProductsDataProvider
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_visual_swatch_attribute.php
     * @magentoCache config disabled
     *
     * @param array $updateData
     * @param array $expectedData
     * @return void
     */
    public function testConfigurableOptionPrices(array $updateData, array $expectedData): void
    {
        $this->updateProducts($updateData);
        $product = $this->productRepository->get('configurable');
        $this->registerProduct($product);
        $configurableOptions = $this->getProductSwatchOptionsBlock()->getJsonConfig();
        $optionsData = $this->json->unserialize($configurableOptions);
        $this->assertArrayHasKey('optionPrices', $optionsData);
        $this->assertEquals($expectedData, array_values($optionsData['optionPrices']));
    }

    /**
     * @return array
     */
    public function childProductsDataProvider(): array
    {
        return [
            [
                'update_data' => [
                    'simple_option_1' => [
                        'special_price' => 50,
                    ],
                    'simple_option_2' => [
                        'special_price' => 58.55,
                    ],
                    'simple_option_3' => [
                        'tier_price' => [
                            [
                                'website_id' => 0,
                                'cust_group' => Group::CUST_GROUP_ALL,
                                'price_qty' => 1,
                                'value_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                                'price' => 75,
                            ],
                        ],
                    ],
                ],
                'expected_data' => [
                    [
                        'baseOldPrice' => ['amount' => 150],
                        'oldPrice' => ['amount' => 150],
                        'basePrice' => ['amount' => 50],
                        'finalPrice' => ['amount' => 50],
                        'tierPrices' => [],
                        'msrpPrice' => ['amount' => null],
                    ],
                    [
                        'baseOldPrice' => ['amount' => 150],
                        'oldPrice' => ['amount' => 150],
                        'basePrice' => ['amount' => 58.55],
                        'finalPrice' => ['amount' => 58.55],
                        'tierPrices' => [],
                        'msrpPrice' => ['amount' => null],
                    ],
                    [
                        'baseOldPrice' => ['amount' => 150],
                        'oldPrice' => ['amount' => 150],
                        'basePrice' => ['amount' => 75],
                        'finalPrice' => ['amount' => 75],
                        'tierPrices' => [],
                        'msrpPrice' => ['amount' => null],
                    ],
                ]
            ],
        ];
    }

    /**
     * Update products.
     *
     * @param array $data
     * @return void
     */
    private function updateProducts(array $data): void
    {
        foreach ($data as $sku => $updateData) {
            $product = $this->productRepository->get($sku);
            $product->addData($updateData);
            $this->productRepository->save($product);
        }
    }

    /**
     * Register the product.
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
    }

    /**
     * Get product swatch options block.
     *
     * @return Configurable
     */
    private function getProductSwatchOptionsBlock(): Configurable
    {
        $this->page->addHandle([
            'default',
            'catalog_product_view',
            'catalog_product_view_type_configurable',
        ]);
        $this->page->getLayout()->generateXml();

        return $this->page->getLayout()->getChildBlock('product.info.options.wrapper', 'swatch_options');
    }
}
