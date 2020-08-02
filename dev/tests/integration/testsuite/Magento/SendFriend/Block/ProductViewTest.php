<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class checks send friend link visibility
 *
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class ProductViewTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var LayoutInterface */
    private $layout;

    /** @var View */
    private $block;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(View::class);
        $this->block->setTemplate('Magento_Catalog::product/view/mailto.phtml');
        $this->registry = $this->objectManager->get(Registry::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->registry->unregister('product');
    }

    /**
     * @return void
     */
    public function testSendFriendLinkDisabled(): void
    {
        $this->registerProduct('simple2');
        $this->assertEmpty($this->block->toHtml());
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/enabled 1
     *
     * @return void
     */
    public function testSendFriendLinkEnabled(): void
    {
        $product = $this->registerProduct('simple2');
        $html = $this->block->toHtml();
        $this->assertStringContainsString('sendfriend/product/send/id/' . $product->getId(), $html);
        $this->assertEquals('Email', trim(strip_tags($html)));
    }

    /**
     * Register product by sku
     *
     * @param string $sku
     * @return ProductInterface
     */
    private function registerProduct(string $sku): ProductInterface
    {
        $product = $this->productRepository->get($sku);
        $this->registry->unregister('product');
        $this->registry->register('product', $product);

        return $product;
    }
}
