<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Model\CartFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;
use Magento\Wishlist\Model\Item\OptionFactory;
use PHPUnit\Framework\TestCase;

/**
 * Tests for wish list item model.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation disabled
 */
class ItemTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var Item */
    private $model;

    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /** @var OptionFactory */
    private $optionFactory;

    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /** @var CartFactory */
    private $cartFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ItemFactory */
    private $itemFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->dataObjectFactory = $this->objectManager->get(DataObjectFactory::class);
        $this->model = $this->objectManager->get(Item::class);
        $this->itemFactory = $this->objectManager->get(ItemFactory::class);
        $this->optionFactory = $this->objectManager->get(OptionFactory::class);
        $this->getWishlistByCustomerId = $this->objectManager->get(GetWishlistByCustomerId::class);
        $this->cartFactory = $this->objectManager->get(CartFactory::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->cartFactory->create()->truncate();

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     *
     * @return void
     */
    public function testBuyRequest(): void
    {
        $product = $this->productRepository->get('simple');
        $option = $this->optionFactory->create(
            ['data' => ['code' => 'info_buyRequest', 'value' => '{"qty":23}']]
        );
        $option->setProduct($product);
        $this->model->addOption($option);
        $buyRequest = $this->model->getBuyRequest();
        $this->assertEquals($buyRequest->getOriginalQty(), 23);
        $this->model->mergeBuyRequest(['qty' => 11, 'additional_data' => 'some value']);
        $buyRequest = $this->model->getBuyRequest();
        $this->assertEquals(
            ['additional_data' => 'some value', 'qty' => 0, 'original_qty' => 11],
            $buyRequest->getData()
        );
    }

    /**
     * @return void
     */
    public function testSetBuyRequest(): void
    {
        $buyRequest = $this->dataObjectFactory->create(
            ['data' => ['field_1' => 'some data', 'field_2' => 234]]
        );
        $this->model->setBuyRequest($buyRequest);
        $this->assertJsonStringEqualsJsonString(
            '{"field_1":"some data","field_2":234,"id":null}',
            $this->model->getData('buy_request')
        );
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testAddItemToCart(): void
    {
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple-1');
        $this->assertNotNull($item);
        $cart = $this->cartFactory->create();
        $this->assertTrue($item->addToCart($cart));
        $this->assertCount(1, $cart->getQuote()->getItemsCollection());
        $this->assertCount(1, $this->getWishlistByCustomerId->execute(1)->getItemCollection());
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testAddItemToCartAndDeleteFromWishList(): void
    {
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple-1');
        $this->assertNotNull($item);
        $cart = $this->cartFactory->create();
        $item->addToCart($cart, true);
        $this->assertCount(1, $cart->getQuote()->getItemsCollection());
        $this->assertCount(0, $this->getWishlistByCustomerId->execute(1)->getItemCollection());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     *
     * @return void
     */
    public function testAddOutOfStockItemToCart(): void
    {
        $product = $this->productRepository->get('simple-out-of-stock');
        $item = $this->itemFactory->create()->setProduct($product);
        $this->expectExceptionObject(new ProductException(__('Product is not salable.')));
        $item->addToCart($this->cartFactory->create());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     *
     * @return void
     */
    public function testAddDisabledItemToCart(): void
    {
        $product = $this->productRepository->get('simple3');
        $item = $this->itemFactory->create()->setProduct($product);
        $this->assertFalse($item->addToCart($this->cartFactory->create()));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_products_not_visible_individually.php
     *
     * @return void
     */
    public function testAddNotVisibleItemToCart(): void
    {
        $product = $this->productRepository->get('simple_not_visible_1');
        $item = $this->itemFactory->create()->setProduct($product)->setStoreId($product->getStoreId());
        $this->assertFalse($item->addToCart($this->cartFactory->create()));
    }
}
