<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View\Description;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks product view block.
 *
 * @see \Magento\Catalog\Block\Product\View
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDbIsolation enabled
 */
class ViewTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var View */
    private $block;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Registry */
    private $registry;

    /** @var LayoutInterface */
    private $layout;

    /** @var Json */
    private $json;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var Description */
    private $descriptionBlock;

    /** @var array */
    private const SHORT_DESCRIPTION_BLOCK_DATA = [
        'at_call' => 'getShortDescription',
        'at_code' => 'short_description',
        'overview' => 'overview',
        'at_label' => 'none',
        'title' => 'Overview',
        'add_attribute' => 'description',
    ];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(View::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->json = $this->objectManager->get(Json::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->descriptionBlock = $this->layout->createBlock(Description::class);
    }

    /**
     * @return void
     */
    public function testSetLayout(): void
    {
        $productView = $this->layout->createBlock(View::class);

        $this->assertInstanceOf(LayoutInterface::class, $productView->getLayout());
    }

    /**
     * @return void
     */
    public function testGetProduct(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);

        $this->assertNotEmpty($this->block->getProduct()->getId());
        $this->assertEquals($product->getId(), $this->block->getProduct()->getId());

        $this->registry->unregister('product');
        $this->block->setProductId($product->getId());

        $this->assertEquals($product->getId(), $this->block->getProduct()->getId());
    }

    /**
     * @return void
     */
    public function testCanEmailToFriend(): void
    {
        $this->assertFalse($this->block->canEmailToFriend());
    }

    /**
     * @return void
     */
    public function testGetAddToCartUrl(): void
    {
        $product = $this->productRepository->get('simple');
        $url = $this->block->getAddToCartUrl($product);

        $this->assertStringMatchesFormat(
            '%scheckout/cart/add/%sproduct/' . $product->getId() . '/',
            $url
        );
    }

    /**
     * @return void
     */
    public function testGetJsonConfig(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);
        $config = $this->json->unserialize($this->block->getJsonConfig());

        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('productId', $config);
        $this->assertEquals($product->getId(), $config['productId']);
    }

    /**
     * @return void
     */
    public function testHasOptions(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);

        $this->assertTrue($this->block->hasOptions());
    }

    /**
     * @return void
     */
    public function testHasRequiredOptions(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);

        $this->assertTrue($this->block->hasRequiredOptions());
    }

    /**
     * @return void
     */
    public function testStartBundleCustomization(): void
    {
        $this->markTestSkipped("Functionality not implemented in Magento 1.x. Implemented in Magento 2");

        $this->assertFalse($this->block->startBundleCustomization());
    }

    /**
     * @magentoAppArea frontend
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     */
    public function testAddToCartBlockInvisibility(): void
    {
        $outOfStockProduct = $this->productRepository->get('simple-out-of-stock');
        $this->registerProduct($outOfStockProduct);
        $this->block->setTemplate('Magento_Catalog::product/view/addtocart.phtml');
        $output = $this->block->toHtml();

        $this->assertNotContains((string)__('Add to Cart'), $output);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testAddToCartBlockVisibility(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);
        $this->block->setTemplate('Magento_Catalog::product/view/addtocart.phtml');
        $output = $this->block->toHtml();

        $this->assertContains((string)__('Add to Cart'), $output);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/product_multistore_different_short_description.php
     * @return void
     */
    public function testProductShortDescription(): void
    {
        $product = $this->productRepository->get('simple-different-short-description');
        $currentStoreId = $this->storeManager->getStore()->getId();
        $output = $this->renderDescriptionBlock($product);

        $this->assertContains('First store view short description', $output);

        $secondStore = $this->storeManager->getStore('fixturestore');
        $this->storeManager->setCurrentStore($secondStore->getId());

        try {
            $product = $this->productRepository->get(
                'simple-different-short-description',
                false,
                $secondStore->getId(),
                true
            );
            $newBlockOutput = $this->renderDescriptionBlock($product, true);

            $this->assertContains('Second store view short description', $newBlockOutput);
        } finally {
            $this->storeManager->setCurrentStore($currentStoreId);
        }
    }

    /**
     * @param ProductInterface $product
     * @param bool $refreshBlock
     * @return string
     */
    private function renderDescriptionBlock(ProductInterface $product, bool $refreshBlock = false): string
    {
        $this->registerProduct($product);
        $descriptionBlock = $this->getDescriptionBlock($refreshBlock);
        $descriptionBlock->addData(self::SHORT_DESCRIPTION_BLOCK_DATA);
        $descriptionBlock->setTemplate('Magento_Catalog::product/view/attribute.phtml');

        return $this->descriptionBlock->toHtml();
    }

    /**
     * Get description block
     *
     * @param bool $refreshBlock
     * @return Description
     */
    private function getDescriptionBlock(bool $refreshBlock): Description
    {
        if ($refreshBlock) {
            $this->descriptionBlock = $this->layout->createBlock(Description::class);
        }

        return $this->descriptionBlock;
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
