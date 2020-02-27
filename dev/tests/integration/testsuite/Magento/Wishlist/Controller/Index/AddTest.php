<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\Wishlist;

/**
 * Test for add product to wish list.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class AddTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /** @var WishlistFactory */
    private $wishlistFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Escaper */
    private $escaper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->wishlistFactory = $this->_objectManager->get(WishlistFactory::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->escaper = $this->_objectManager->get(Escaper::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     *
     * @return void
     */
    public function testAddActionProductNameXss(): void
    {
        $this->customerSession->setCustomerId(1);
        $product = $this->productRepository->get('product-with-xss');
        $escapedProductName = $this->escaper->escapeHtml($product->getName());
        $expectedMessage = sprintf("\n%s has been added to your Wish List.", $escapedProductName)
            . " Click <a href=\"http://localhost/index.php/\">here</a> to continue shopping.";
        $this->performAddToWishListRequest(['product' => $product->getId()]);
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_SUCCESS);
        $wishlist = $this->getWishListByCustomerId(1);
        $this->assertCount(1, $wishlist->getItemCollection());
        $this->assertRedirect($this->stringContains('wishlist/index/index/wishlist_id/' . $wishlist->getId()));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_configurable_product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testAddConfigurableProductToWishList(): void
    {
        $this->customerSession->setCustomerId(1);
        $product = $this->productRepository->get('Configurable product');
        $expectedMessage = sprintf("\n%s has been added to your Wish List.", $product->getName())
            . " Click <a href=\"http://localhost/index.php/\">here</a> to continue shopping.";
        $this->performAddToWishListRequest(['product' => $product->getId()]);
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_SUCCESS);
        $wishlist = $this->getWishListByCustomerId(1);
        $this->assertCount(1, $wishlist->getItemCollection());
        $this->assertRedirect($this->stringContains('wishlist/index/index/wishlist_id/' . $wishlist->getId()));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     *
     * @return void
     */
    public function testAddDisabledProductToWishList(): void
    {
        $expectedMessage = $this->escaper->escapeHtml("We can't specify a product.");
        $this->customerSession->setCustomerId(1);
        $product = $this->productRepository->get('simple3');
        $this->performAddToWishListRequest(['product' => $product->getId()]);
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('wishlist/'));
    }

    /**
     * @return void
     */
    public function testAddToWishListWithoutParams(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->performAddToWishListRequest([]);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertRedirect($this->stringContains('wishlist/'));
    }

    /**
     * @return void
     */
    public function testAddNotExistingProductToWishList(): void
    {
        $this->customerSession->setCustomerId(1);
        $expectedMessage = $this->escaper->escapeHtml("We can't specify a product.");
        $this->performAddToWishListRequest(['product' => 989]);
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('wishlist/'));
    }

    /**
     * @return void
     */
    public function testAddToNotExistingWishList(): void
    {
        $expectedMessage = $this->escaper->escapeHtml("The requested Wish List doesn't exist.");
        $this->customerSession->setCustomerId(1);
        $this->performAddToWishListRequest(['wishlist_id' => 989]);
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_ERROR);
        $this->assert404NotFound();
    }

    /**
     * Perform request add item to wish list.
     *
     * @param array $params
     * @return void
     */
    private function performAddToWishListRequest(array $params): void
    {
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/add');
    }

    /**
     * Get customer wish list.
     *
     * @param int $customerId
     * @return Wishlist
     */
    private function getWishListByCustomerId(int $customerId): Wishlist
    {
        return $this->wishlistFactory->create()->loadByCustomerId($customerId);
    }
}
