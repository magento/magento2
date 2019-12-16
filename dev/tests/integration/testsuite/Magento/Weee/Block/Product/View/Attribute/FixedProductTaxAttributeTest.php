<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Block\Product\View\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Pricing\Render as CatalogPricingRender;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class checks FPT attribute displaying on frontend
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class FixedProductTaxAttributeTest extends TestCase
{
    /** @var array  */
    private const TEST_TAX_DATA = [
        [
            'region_id' => '1',
            'country' => 'US',
            'val' => '',
            'value' => '5',
            'website_id' => '1',
            'state' => '',
        ]
    ];

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var string */
    private $attributeCode;

    /** @var LayoutInterface */
    private $layout;

    /** @var ListProduct */
    private $productListBlock;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->productListBlock = $this->layout->createBlock(ListProduct::class);
        $this->attributeCode = 'fixed_product_attribute';
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->registry->unregister('product');

        parent::tearDown();
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 0
     */
    public function testFPTCategoryPageIncludingFPTOnly(): void
    {
        $this->prepareLayoutCategoryPage();
        $product = $this->updateProduct('simple2', self::TEST_TAX_DATA);
        $productPrice = $this->productListBlock->getProductPrice($product);
        $this->assertEquals('$15.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 1
     */
    public function testFPTCategoryPageIncludingFPTAndDescription(): void
    {
        $this->prepareLayoutCategoryPage();
        $product = $this->updateProduct('simple2', self::TEST_TAX_DATA);
        $productPrice = $this->productListBlock->getProductPrice($product);
        $this->assertContains('data-label="fixed&#x20;product&#x20;tax"', $productPrice);
        $this->assertEquals('$15.00$5.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 2
     */
    public function testFPTCategoryPageExcludingFPTIncludingDescriptionAndPrice(): void
    {
        $this->prepareLayoutCategoryPage();
        $product = $this->updateProduct('simple2', self::TEST_TAX_DATA);
        $productPrice = $this->productListBlock->getProductPrice($product);
        $this->assertContains('data-label="fixed&#x20;product&#x20;tax"', $productPrice);
        $this->assertEquals('$10.00$5.00$15.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 3
     */
    public function testFPTCategoryPageExcludingFPT(): void
    {
        $this->prepareLayoutCategoryPage();
        $product = $this->updateProduct('simple2', self::TEST_TAX_DATA);
        $productPrice = $this->productListBlock->getProductPrice($product);
        $this->assertEquals('$10.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 0
     */
    public function testFPTProductPageIncludingFPTOnly(): void
    {
        $product = $this->updateProduct('simple2', self::TEST_TAX_DATA);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertEquals('$15.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 1
     */
    public function testFPTProductPageIncludingFPTAndDescription(): void
    {
        $product = $this->updateProduct('simple2', self::TEST_TAX_DATA);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertContains('data-label="fixed&#x20;product&#x20;tax"', $productPrice);
        $this->assertEquals('$15.00$5.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 2
     */
    public function testFPTProductPageExcludingFPTIncludingDescriptionAndPrice(): void
    {
        $product = $this->updateProduct('simple2', self::TEST_TAX_DATA);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertContains('data-label="fixed&#x20;product&#x20;tax"', $productPrice);
        $this->assertEquals('$10.00$5.00$15.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 3
     */
    public function testFPTProductPageExcludingFPT(): void
    {
        $product = $this->updateProduct('simple2', self::TEST_TAX_DATA);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertEquals('$10.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * Update product
     *
     * @param string $productSku
     * @param array $data
     * @return ProductInterface
     */
    private function updateProduct(string $productSku, array $data): ProductInterface
    {
        $product = $this->productRepository->get($productSku);
        $product->addData([$this->attributeCode => $data]);

        return $this->productRepository->save($product);
    }

    /**
     * Prepare layout for category page view
     *
     * @return void
     */
    private function prepareLayoutCategoryPage(): void
    {
        $this->layout->createBlock(RendererPool::class, 'render.product.prices');
        $block = $this->objectManager->create(Render::class);
        $block->setPriceRenderHandle('catalog_product_prices');
        $block->setLayout($this->layout);
        $this->layout->addBlock($block, 'product.price.render.default');
    }

    /**
     * Prepare layout for product page
     *
     * @return CatalogPricingRender
     */
    private function prepareLayoutProductPage(): CatalogPricingRender
    {
        $render = $this->objectManager->create(Render::class);
        $render->setPriceRenderHandle('catalog_product_prices');
        $this->layout->addBlock($render, 'product.price.render.default');
        $block = $this->objectManager->create(CatalogPricingRender::class);
        $block->setPriceRender('product.price.render.default');
        $block->setPriceTypeCode('final_price');
        $this->layout->addBlock($block, 'render.product.prices');
        $block->setLayout($this->layout);
        $render->setLayout($this->layout);

        return $block;
    }

    /**
     * Register the product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
    }
}
