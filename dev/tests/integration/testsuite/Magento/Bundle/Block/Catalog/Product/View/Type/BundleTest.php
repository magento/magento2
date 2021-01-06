<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Catalog\Product\View\Type;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

/**
 * Class checks bundle product view behaviour
 *
 * @magentoDataFixture Magento/Bundle/_files/product.php
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 * @see \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
 */
class BundleTest extends TestCase
{
    /** @var Bundle */
    private $block;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var LayoutInterface */
    private $layout;

    /** @var SerializerInterface */
    private $json;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Bundle::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
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
     * Test for method \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle::getJsonConfig
     *
     * @return void
     */
    public function testGetJsonConfig(): void
    {
        $product = $this->updateProduct('bundle-product', ['price_type' => Price::PRICE_TYPE_DYNAMIC]);
        $this->registerProduct($product);
        $this->updateProduct('simple', ['special_price' => 5]);
        $config = $this->json->unserialize($this->block->getJsonConfig());
        $options = current($config['options']);
        $selection = current($options['selections']);
        $this->assertEquals(10, $selection['prices']['oldPrice']['amount']);
        $this->assertEquals(5, $selection['prices']['basePrice']['amount']);
        $this->assertEquals(5, $selection['prices']['finalPrice']['amount']);
    }

    /**
     * Check that selection qty is calculated correctly for Bundle config.
     *
     * @return void
     */
    public function testGetJsonConfigWithPreconfiguredValues(): void
    {
        $optionQty = 3;
        $bundleProduct = $this->productRepository->get('bundle-product');
        $bundleSelection = $this->productRepository->get('simple');

        /** @var Type $typeInstance */
        $typeInstance = $bundleProduct->getTypeInstance();
        $typeInstance->setStoreFilter($bundleProduct->getStoreId(), $bundleProduct);
        $optionCollection = $typeInstance->getOptionsCollection($bundleProduct);
        $optionId = $optionCollection->getFirstItem()->getId();
        $preconfiguredValues = $this->objectManager->get(DataObjectFactory::class)->create([
            'bundle_option' => [
                $optionId => [$bundleSelection->getId()]
            ],
            'bundle_option_qty' => [
                $optionId => $optionQty
            ],
        ]);

        /** @var ProductHelper $productHelper */
        $productHelper = $this->objectManager->get(ProductHelper::class);
        $productHelper->prepareProductOptions($bundleProduct, $preconfiguredValues);
        $this->registerProduct($bundleProduct);

        $resultConfig = $this->json->unserialize($this->block->getJsonConfig());
        $this->assertTrue(isset($resultConfig['options'][$optionId]['selections'][$optionId]['qty']));
        $this->assertEquals($optionQty, $resultConfig['options'][$optionId]['selections'][$optionId]['qty']);
    }

    /**
     * @dataProvider isSalableForStockStatusProvider
     *
     * @param bool $isSalable
     * @param string $expectedValue
     * @return void
     */
    public function testStockStatusView(bool $isSalable, string $expectedValue): void
    {
        $product = $this->productRepository->get('bundle-product');
        $product->setAllItemsSalable($isSalable);
        $this->block->setTemplate('Magento_Bundle::catalog/product/view/type/bundle.phtml');
        $result = $this->renderBlockHtml($product);
        $this->assertEquals($expectedValue, trim(strip_tags($result)));
    }

    /**
     * @return array
     */
    public function isSalableForStockStatusProvider(): array
    {
        return [
            'is_salable' => [
                'is_salable' => true,
                'expected_value' => 'In stock',
            ],
            'is_not_salable' => [
                'is_salable' => false,
                'expected_value' => 'Out of stock',
            ],
        ];
    }

    /**
     * @dataProvider isSalableForCustomizeButtonProvider
     *
     * @param bool $isSalable
     * @param string $expectedValue
     * @return void
     */
    public function testCustomizeButton(bool $isSalable, string $expectedValue): void
    {
        $product = $this->productRepository->get('bundle-product');
        $product->setSalable($isSalable);
        $this->block->setTemplate('Magento_Bundle::catalog/product/view/customize.phtml');
        $result = $this->renderBlockHtml($product);
        $this->assertEquals($expectedValue, trim(strip_tags($result)));
    }

    /**
     * @return array
     */
    public function isSalableForCustomizeButtonProvider(): array
    {
        return [
            'is_salable' => [
                'is_salable' => true,
                'expected_value' => 'Customize and Add to Cart',
            ],
            'is_not_salable' => [
                'is_salable' => false,
                'expected_value' => '',
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/empty_bundle_product.php
     *
     * @param bool $isSalable
     * @param string $expectedValue
     * @return void
     */
    public function testCustomizeButtonProductWithoutOptions(): void
    {
        $product = $this->productRepository->get('bundle-product');
        $product->setSalable(true);
        $this->block->setTemplate('Magento_Bundle::catalog/product/view/customize.phtml');
        $result = $this->renderBlockHtml($product);
        $this->assertEmpty(trim(strip_tags($result)));
    }

    /**
     * Update product
     *
     * @param ProductInterface|string $productSku
     * @param array $data
     * @return ProductInterface
     */
    private function updateProduct(string $productSku, array $data): ProductInterface
    {
        $product = $this->productRepository->get($productSku);
        $product->addData($data);

        return $this->productRepository->save($product);
    }

    /**
     * Register product
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
     * Render block output
     *
     * @param ProductInterface $product
     * @return string
     */
    private function renderBlockHtml(ProductInterface $product): string
    {
        $this->registerProduct($product);

        return $this->block->toHtml();
    }
}
