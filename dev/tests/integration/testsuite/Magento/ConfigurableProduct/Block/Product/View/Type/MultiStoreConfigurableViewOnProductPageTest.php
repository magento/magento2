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
use Magento\TestFramework\Store\ExecuteInStoreContext;
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

    /** @var LayoutInterface */
    private $layout;

    /** @var SerializerInterface */
    private $serializer;

    /** @var ProductResource */
    private $productResource;

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_different_option_labeles_per_stores.php
     *
     * @dataProvider expectedLabelsDataProvider
     *
     * @param array $expectedStoreData
     * @param array $expectedSecondStoreData
     * @return void
     */
    public function testMultiStoreLabelView(array $expectedStoreData, array $expectedSecondStoreData): void
    {
        $this->executeInStoreContext->execute('default', [$this, 'assertProductLabel'], $expectedStoreData);
        $this->executeInStoreContext->execute('fixturestore', [$this, 'assertProductLabel'], $expectedSecondStoreData);
    }

    /**
     * @return array
     */
    public function expectedLabelsDataProvider(): array
    {
        return [
            [
                'options_first_store' => [
                    'simple_option_1_default_store' => [
                        'label' => 'Option 1 Default Store',
                    ],
                    'simple_option_2_default_store' => [
                        'label' => 'Option 2 Default Store',
                    ],
                    'simple_option_3_default_store' => [
                        'label' => 'Option 3 Default Store',
                    ],
                ],
                'options_second_store' => [
                    'simple_option_1_default_store' => [
                        'label' => 'Option 1 Second Store',
                    ],
                    'simple_option_2_default_store' => [
                        'label' => 'Option 2 Second Store',
                    ],
                    'simple_option_3_default_store' => [
                        'label' => 'Option 3 Second Store',
                    ],
                ],
            ],
        ];
    }

    /**
     * Assert configurable product labels config
     *
     * @param $expectedStoreData
     * @return void
     */
    public function assertProductLabel($expectedStoreData): void
    {
        $product = $this->productRepository->get('configurable', false, null, true);
        $config = $this->getBlockConfig($product)['attributes'] ?? null;
        $this->assertNotNull($config);
        $this->assertAttributeConfig($expectedStoreData, reset($config));
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
        $this->executeInStoreContext->execute('default', [$this, 'assertProductConfig'], $expectedProducts);
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'assertProductConfig'],
            $expectedSecondStoreProducts
        );
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
     * Assert configurable product config
     *
     * @param $expectedProducts
     * @return void
     */
    public function assertProductConfig($expectedProducts): void
    {
        $product = $this->productRepository->get('configurable', false, null, true);
        $config = $this->getBlockConfig($product)['index'] ?? null;
        $this->assertNotNull($config);
        $this->assertProducts($expectedProducts, $config);
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
        $productToUpdate = $product->getTypeInstance()->getUsedProductCollection($product)
            ->addStoreFilter($storeCode)
            ->setPageSize(1)
            ->getFirstItem();

        $this->assertNotEmpty($productToUpdate->getData(), 'Configurable product does not have a child');
        $this->executeInStoreContext->execute($storeCode, [$this, 'setProductDisabled'], $productToUpdate);
    }

    /**
     * Assert product options display per stores
     *
     * @param array $expectedProducts
     * @param array $config
     * @return void
     */
    private function assertProducts(array $expectedProducts, array $config): void
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
    public function setProductDisabled(ProductInterface $product): void
    {
        $product->setStatus(Status::STATUS_DISABLED);
        $this->productRepository->save($product);
    }

    /**
     * Get block config
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getBlockConfig(ProductInterface $product): array
    {
        $block = $this->layout->createBlock(Configurable::class);
        $block->setProduct($product);

        return $this->serializer->unserialize($block->getJsonConfig());
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
        $skus = array_keys($expectedData);
        $idBySkuMap = $this->productResource->getProductsIdsBySkus($skus);
        array_walk($actualOptions['options'], function (&$option) {
            unset($option['id']);
        });
        foreach ($expectedData as $sku => &$option) {
            $option['products'] = [$idBySkuMap[$sku]];
        }
        $this->assertEquals(array_values($expectedData), $actualOptions['options']);
    }
}
