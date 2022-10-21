<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Model\CartFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;

/**
 * Test for add product to cart from wish list.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class CartTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /** @var CartFactory */
    private $cartFactory;

    /** @var Escaper */
    private $escaper;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->getWishlistByCustomerId = $this->_objectManager->get(GetWishlistByCustomerId::class);
        $this->cartFactory = $this->_objectManager->get(CartFactory::class);
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->fixtures = $this->_objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     *
     * @return void
     */
    public function testAddSimpleProductToCart(): void
    {
        $this->customerSession->setCustomerId(1);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple-1');
        $this->assertNotNull($item);
        $this->performAddToCartRequest(['item' => $item->getId(), 'qty' => 3]);
        $message = sprintf("\n" . 'You added %s to your ' .
            '<a href="http://localhost/index.php/checkout/cart/">shopping cart</a>.', $item->getName());
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_SUCCESS);
        $this->assertCount(0, $this->getWishlistByCustomerId->execute(1)->getItemCollection());
        $cart = $this->cartFactory->create();
        $this->assertEquals(1, $cart->getItemsCount());
        $this->assertEquals(3, $cart->getItemsQty());
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_configurable_product.php
     *
     * @return void
     */
    public function testAddItemWithNotChosenOptionToCart(): void
    {
        $this->customerSession->setCustomerId(1);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'Configurable product');
        $this->assertNotNull($item);
        $this->performAddToCartRequest(['item' => $item->getId(), 'qty' => 1]);
        $redirectUrl = sprintf("wishlist/index/configure/id/%s/product_id/%s", $item->getId(), $item->getProductId());
        $this->assertRedirect($this->stringContains($redirectUrl));
        $message = 'You need to choose options for your item.';
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_NOTICE);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testAddNotExistingItemToCart(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->performAddToCartRequest(['item' => 989]);
        $this->assertRedirect($this->stringContains('wishlist/index/'));
    }

    /**
     * Add wishlist item with related Products to Cart.
     *
     * @return void
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    #[
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
    ]
    public function testAddItemWithRelatedProducts(): void
    {
        $firstProductId = $this->fixtures->get('product1')->getId();
        $secondProductID = $this->fixtures->get('product2')->getId();
        $relatedIds = $expectedAddedIds = [$firstProductId, $secondProductID];

        $this->customerSession->setCustomerId(1);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple-1');
        $this->assertNotNull($item);

        $this->performAddToCartRequest([
            'item' => $item->getId(),
            'qty' => 1,
            'related_product' => implode(',', $relatedIds),
        ]);

        $this->assertCount(0, $this->getWishlistByCustomerId->execute(1)->getItemCollection());
        $cart = $this->cartFactory->create();
        $this->assertEquals(3, $cart->getItemsCount());
        $expectedAddedIds[] = $item->getProductId();
        foreach ($expectedAddedIds as $addedId) {
            $this->assertContains($addedId, $cart->getProductIds());
        }
    }

    /**
     * Perform request add to cart from wish list.
     *
     * @param array $params
     * @return void
     */
    private function performAddToCartRequest(array $params): void
    {
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/cart');
    }
}
