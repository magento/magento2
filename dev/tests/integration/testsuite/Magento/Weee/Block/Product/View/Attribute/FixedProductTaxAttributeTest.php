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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FixedProductTaxAttributeTest extends TestCase
{
    /** @var array */
    private $textTaxData;

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

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var Session */
    private $customerSession;

    /** @var int */
    private $baseWebsiteId;

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
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->baseWebsiteId = (int) $this->storeManager->getWebsite('base')->getId();
        $this->textTaxData = [
            [
                'country' => 'US',
                'val' => '',
                'value' => '5',
                'website_id' => $this->baseWebsiteId,
                'state' => '',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->registry->unregister('product');
        $this->registry->unregister('current_product');
        $this->customerSession->logout();

        parent::tearDown();
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 0
     *
     * @return void
     */
    public function testFPTCategoryPageIncludingFPTOnly(): void
    {
        $this->prepareLayoutCategoryPage();
        $product = $this->updateProduct('simple2', $this->textTaxData);
        $productPrice = $this->productListBlock->getProductPrice($product);
        $this->assertEquals('$15.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 1
     *
     * @return void
     */
    public function testFPTCategoryPageIncludingFPTAndDescription(): void
    {
        $this->prepareLayoutCategoryPage();
        $product = $this->updateProduct('simple2', $this->textTaxData);
        $productPrice = $this->productListBlock->getProductPrice($product);
        $this->assertContains('data-label="fixed&#x20;product&#x20;tax"', $productPrice);
        $this->assertEquals('$15.00$5.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 2
     *
     * @return void
     */
    public function testFPTCategoryPageExcludingFPTIncludingDescriptionAndPrice(): void
    {
        $this->prepareLayoutCategoryPage();
        $product = $this->updateProduct('simple2', $this->textTaxData);
        $productPrice = $this->productListBlock->getProductPrice($product);
        $this->assertContains('data-label="fixed&#x20;product&#x20;tax"', $productPrice);
        $this->assertEquals('$10.00$5.00$15.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display_list 3
     *
     * @return void
     */
    public function testFPTCategoryPageExcludingFPT(): void
    {
        $this->prepareLayoutCategoryPage();
        $product = $this->updateProduct('simple2', $this->textTaxData);
        $productPrice = $this->productListBlock->getProductPrice($product);
        $this->assertEquals('$10.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display 0
     *
     * @return void
     */
    public function testFPTProductPageIncludingFPTOnly(): void
    {
        $product = $this->updateProduct('simple2', $this->textTaxData);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertEquals('$15.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display 1
     *
     * @return void
     */
    public function testFPTProductPageIncludingFPTAndDescription(): void
    {
        $product = $this->updateProduct('simple2', $this->textTaxData);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertContains('data-label="fixed&#x20;product&#x20;tax"', $productPrice);
        $this->assertEquals('$15.00$5.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display 2
     *
     * @return void
     */
    public function testFPTProductPageExcludingFPTIncludingDescriptionAndPrice(): void
    {
        $product = $this->updateProduct('simple2', $this->textTaxData);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertContains('data-label="fixed&#x20;product&#x20;tax"', $productPrice);
        $this->assertEquals('$10.00$5.00$15.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display 3
     *
     * @return void
     */
    public function testFPTProductPageExcludingFPT(): void
    {
        $product = $this->updateProduct('simple2', $this->textTaxData);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertEquals('$10.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoConfigFixture default/catalog/price/scope 1
     * @magentoConfigFixture fixture_second_store_store tax/weee/enable 1
     * @magentoConfigFixture fixture_second_store_store tax/weee/display 2
     *
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     *
     * @return void
     */
    public function testFPTPerWebsites(): void
    {
        $currentStore = $this->storeManager->getStore();
        try {
            $secondStore = $this->storeManager->getStore('fixture_second_store');
            $taxData = [
                [
                    'region_id' => '1',
                    'country' => 'US',
                    'val' => '',
                    'value' => '5',
                    'website_id' => $secondStore->getWebsiteId(),
                    'state' => '',
                ]
            ];
            $this->storeManager->setCurrentStore($secondStore);
            $product = $this->updateProduct('simple-on-two-websites', $taxData);
            $this->registerProduct($product);
            $block = $this->prepareLayoutProductPage();
            $productPrice = $block->toHtml();
            $this->assertEquals('$10.00$5.00$15.00', preg_replace('/\s+/', '', strip_tags($productPrice)));
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display 0
     *
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     * @magentoDataFixture Magento/Customer/_files/customer_one_address.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testApplyTwoFPTForCustomer(): void
    {
        $email = 'customer_one_address@test.com';
        $expectedPrice = '$30.00';
        $taxData = [
            [
                'country' => 'US',
                'val' => '',
                'value' => '5',
                'website_id' => $this->baseWebsiteId,
                'state' => '',
            ],
            [
                'country' => 'US',
                'val' => '',
                'value' => '15',
                'website_id' => $this->baseWebsiteId,
                'state' => 1,
            ]
        ];
        $this->loginCustomerByEmail($email);
        $product = $this->updateProduct('simple2', $taxData);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertEquals($expectedPrice, preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/defaults/country GB
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display 0
     *
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testApplyFPTWithoutAddressCustomer(): void
    {
        $email = 'customer5@example.com';
        $expectedPrice =  '$10.00';
        $taxData = [
            [
                'country' => 'US',
                'val' => '',
                'value' => '5',
                'website_id' => $this->baseWebsiteId,
                'state' => '',
            ],
            [
                'country' => 'US',
                'val' => '',
                'value' => '15',
                'website_id' => $this->baseWebsiteId,
                'state' => 1,
            ],
        ];
        $this->loginCustomerByEmail($email);
        $product = $this->updateProduct('simple2', $taxData);
        $this->registerProduct($product);
        $block = $this->prepareLayoutProductPage();
        $productPrice = $block->toHtml();
        $this->assertEquals($expectedPrice, preg_replace('/\s+/', '', strip_tags($productPrice)));
    }

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/display 0
     *
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer_with_uk_address.php
     *
     * @return void
     */
    public function testApplyFPTWithForeignCountryAddress(): void
    {
        $this->loginCustomerByEmail('customer_uk_address@test.com');
        $product = $this->updateProduct('simple2', $this->textTaxData);
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
        $this->registry->unregister('current_product');
        $this->registry->register('current_product', $product);
    }

    /**
     * Login customer by email
     *
     * @param string $email
     * @return void
     */
    private function loginCustomerByEmail(string $email): void
    {
        $customer = $this->customerRepository->get($email);
        $this->customerSession->loginById($customer->getId());
    }
}
