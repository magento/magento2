<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Catalog\Product\View\AddTo;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Checks add to wishlist button on product page.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation disabled
 */
class WishlistTest extends TestCase
{
    private const ADD_TO_WISHLIST_XPATH = "//a[@data-action='add-to-wishlist']"
    . "/span[contains(text(), 'Add to Wish List')]";

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var Wishlist */
    private $block;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->block = $this->objectManager->get(LayoutInterface::class)
            ->createBlock(Wishlist::class)->setTemplate('Magento_Wishlist::catalog/product/view/addto/wishlist.phtml');
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
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testAddToWishListVisible(): void
    {
        $product = $this->productRepository->get('simple2');
        $this->registerProduct($product);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::ADD_TO_WISHLIST_XPATH, $this->block->toHtml())
        );
    }

    /**
     * @magentoConfigFixture current_store wishlist/general/active 0
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testAddToWishListNotVisible(): void
    {
        $product = $this->productRepository->get('simple2');
        $this->registerProduct($product);
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(self::ADD_TO_WISHLIST_XPATH, $this->block->toHtml())
        );
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
}
