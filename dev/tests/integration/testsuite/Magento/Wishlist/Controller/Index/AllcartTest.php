<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Checkout\Model\CartFactory;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;

/**
 * Test for add all products to cart from wish list.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class AllcartTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /** @var CartModel */
    private $cart;

    /** @var Escaper */
    private $escaper;

    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->cart = $this->_objectManager->get(CartFactory::class)->create();
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->getWishlistByCustomerId = $this->_objectManager->get(GetWishlistByCustomerId::class);
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
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_product_qty_increments.php
     *
     * @return void
     */
    public function testAddProductQtyIncrementToCartFromWishList(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->performAddAllToCartRequest();
        $wishlistCollection = $this->getWishlistByCustomerId->execute(1)->getItemCollection();
        $this->assertCount(1, $wishlistCollection);
        $this->assertCount(0, $this->cart->getQuote()->getItemsCollection());
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple');
        $this->assertNotNull($item);
        $expectedMessage = $this->escaper->escapeHtml(
            sprintf('You can buy this product only in quantities of 5 at a time for "%s".', $item->getName())
        );
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_ERROR);
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     *
     * @return void
     */
    public function testAddAllProductToCartFromWishList(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->performAddAllToCartRequest();
        $quoteCollection = $this->cart->getQuote()->getItemsCollection();
        $this->assertCount(1, $quoteCollection);
        $item = $quoteCollection->getFirstItem();
        $expectedMessage = $this->escaper->escapeHtml(
            sprintf('1 product(s) have been added to shopping cart: "%s".', $item->getName())
        );
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_SUCCESS);
    }

    /**
     * Perform add all products to cart from wish list request.
     *
     * @return void
     */
    private function performAddAllToCartRequest(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/allcart');
    }
}
