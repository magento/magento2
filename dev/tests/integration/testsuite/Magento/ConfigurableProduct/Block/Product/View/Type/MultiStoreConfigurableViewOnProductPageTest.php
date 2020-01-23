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
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class check configurable product options displaying per stores
 *
 * @magentoDbIsolation disabled
 */
class MultiStoreConfigurableViewOnProductPageTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var Configurable */
    private $block;

    /** @var LayoutInterface */
    private $layout;

    /** @var SerializerInterface */
    private $serializer;

    /** @var ProductResource */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Configurable::class);
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_different_option_labeles_per_stores.php
     *
     * @dataProvider expectedLabelsDataProvider
     *
     * @param array $expectedStoreData
     * @param array $expectedSecondSoreData
     * @return void
     */
    public function testMultiStoreLabelView(array $expectedStoreData, array $expectedSecondSoreData): void
    {
        $this->assertProductLabelConfigDataPerStore($expectedStoreData);
        $this->assertProductLabelConfigDataPerStore($expectedSecondSoreData, 'fixturestore', true);
    }

    /**
     * @return array
     */
    public function expectedLabelsDataProvider(): array
    {
        return [
            [
                'options_first_store' => [
                    [
                        'label' => 'Option 1 Default Store',
                        'sku' => 'simple_option_1_default_store',
                    ],
                    [
                        'label' => 'Option 2 Default Store',
                        'sku' => 'simple_option_2_default_store',
                    ],
                    [
                        'label' => 'Option 3 Default Store',
                        'sku' => 'simple_option_3_default_store',
                    ],
                ],
                'options_second_store' => [
                    [
                        'label' => 'Option 1 Second Store',
                        'sku' => 'simple_option_1_default_store',
                    ],
                    [
                        'label' => 'Option 2 Second Store',
                        'sku' => 'simple_option_2_default_store',
                    ],
                    [
                        'label' => 'Option 3 Second Store',
                        'sku' => 'simple_option_3_default_store',
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_two_websites.php
     *
     * @dataProvider expectedProductDataProvider
     *
     * @param array $expectedProducts
     * @param array $expectedSecondStoreProducts
     * @return void
     */
    public function testMultiStoreOptionsView(array $expectedProducts, array $expectedSecondStoreProducts): void
    {
        $this->prepareConfigurableProduct('configurable', 'fixture_second_store');
        $this->assertProductConfigPerStore($expectedProducts);
        $this->assertProductConfigPerStore($expectedSecondStoreProducts, 'fixture_second_store', true);
    }

    /**
     * @return array
     */
    public function expectedProductDataProvider(): array
    {
        return [
            [
                'expected_store_products' => ['simple_option_1', 'simple_option_2'],
                'expected_second_store_products' => ['simple_option_2'],
            ],
        ];
    }

    /**
     * Prepare configurable product to test
     *
     * @param string $sku
     * @param string $storeCode
     * @return void
     */
    private function prepareConfigurableProduct(string $sku, string $storeCode): void
    {
        $product = $this->productRepository->get($sku, false, null, true);
        $productToUpdate = $product->getTypeInstance()->getUsedProductCollection($product)->getFirstItem();
        $this->assertNotEmpty($productToUpdate->getData(), 'Configurable product does not have a child');
        $this->setProductDisabledPerStore($productToUpdate, $storeCode);
    }

    /**
     * Assert product options display per stores
     *
     * @param array $expectedProducts
     * @param array $config
     * @return void
     */
    private function assertProductConfig(array $expectedProducts, array $config): void
    {
        $this->assertCount(count($expectedProducts), $config);
        $idsBySkus = $this->productResource->getProductsIdsBySkus($expectedProducts);

        foreach ($idsBySkus as $productId) {
            $this->assertArrayHasKey($productId, $config);
        }
    }

    /**
     * Set product status attribute to disabled
     *
     * @param ProductInterface $product
     * @param string $storeCode
     * @return void
     */
    private function setProductDisabledPerStore(ProductInterface $product, string $storeCode)
    {
        $currentStore = $this->storeManager->getStore();
        try {
            $this->storeManager->setCurrentStore($storeCode);
            $product->setStatus(Status::STATUS_DISABLED);
            $this->productRepository->save($product);
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }

    /**
     * Assert configurable product config per stores
     *
     * @param array $expectedProducts
     * @param string $storeCode
     * @param bool $refreshBlock
     * @return void
     */
    private function assertProductConfigPerStore(
        array $expectedProducts,
        string $storeCode = 'default',
        bool $refreshBlock = false
    ): void {
        $currentStore = $this->storeManager->getStore();
        try {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($storeCode);
            }
            $product = $this->productRepository->get('configurable', false, null, true);
            $config = $this->getBlockConfig($product, $refreshBlock)['index'] ?? null;
            $this->assertNotNull($config);
            $this->assertProductConfig($expectedProducts, $config);
        } finally {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($currentStore);
            }
        }
    }

    /**
     * Assert configurable product labels config per stores
     *
     * @param array $expectedStoreData
     * @param string $storeCode
     * @param bool $refreshBlock
     * @return void
     */
    private function assertProductLabelConfigDataPerStore(
        array $expectedStoreData,
        string $storeCode = 'default',
        bool $refreshBlock = false
    ): void {
        $currentStore = $this->storeManager->getStore();
        try {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($storeCode);
            }
            $product = $this->productRepository->get('configurable', false, null, true);
            $config = $this->getBlockConfig($product, $refreshBlock)['attributes'] ?? null;
            $this->assertNotNull($config);
            $this->assertAttributeConfig($expectedStoreData, reset($config));
        } finally {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($currentStore);
            }
        }
    }

    /**
     * Get view block
     *
     * @param bool $refresh
     * @return Configurable
     */
    private function getBlock(bool $refresh = false): Configurable
    {
        if ($refresh) {
            $this->block = $this->layout->createBlock(Configurable::class);
        }
        return $this->block;
    }

    /**
     * Get block config
     *
     * @param ProductInterface $product
     * @param bool $refreshBlock
     * @return array
     */
    private function getBlockConfig(ProductInterface $product, bool $refreshBlock): array
    {
        $this->getBlock($refreshBlock)->setProduct($product);

        return $this->serializer->unserialize($this->getBlock()->getJsonConfig());
    }

    /**
     * Assert configurable product config
     *
     * @param array $expectedData
     * @param array $actualOptions
     * @return void
     */
    private function assertAttributeConfig(array $expectedData, array $actualOptions): void
    {
        $skus = array_column($expectedData, 'sku');
        $idBySkuMap = $this->productResource->getProductsIdsBySkus($skus);
        foreach ($expectedData as &$option) {
            $sku = $option['sku'];
            unset($option['sku']);
            $option['products'] = [$idBySkuMap[$sku]];
            $found = false;
            foreach ($actualOptions['options'] as $actualOption) {
                if ($option['label'] === $actualOption['label']) {
                    unset($actualOption['id']);
                    $this->assertEquals($option, $actualOption);
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, sprintf('The option with %s label is not loaded', $option['label']));
        }
    }
}
