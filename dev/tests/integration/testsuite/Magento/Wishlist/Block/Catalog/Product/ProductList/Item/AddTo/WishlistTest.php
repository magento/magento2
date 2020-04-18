<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks add to wishlist button on category page.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation disabled
 */
class WishlistTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

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
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->block = $this->objectManager->get(LayoutInterface::class)
            ->createBlock(Wishlist::class)->setTemplate('Magento_Wishlist::catalog/product/list/addto/wishlist.phtml');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testAddToWishListVisible(): void
    {
        $product = $this->productRepository->get('simple2');
        $html = $this->block->setProduct($product)->toHtml();
        $this->assertEquals('Add to Wish List', trim(strip_tags($html)));
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
        $this->assertEmpty($this->block->setProduct($product)->toHtml());
    }
}
