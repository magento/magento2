<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;
use Zend\Stdlib\Parameters;

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

    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Escaper */
    private $escaper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->getWishlistByCustomerId = $this->_objectManager->get(GetWishlistByCustomerId::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->escaper = $this->_objectManager->get(Escaper::class);
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
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     *
     * @return void
     */
    public function testAddActionProductNameXss(): void
    {
        $this->prepareReferer();
        $this->customerSession->setCustomerId(1);
        $product = $this->productRepository->get('product-with-xss');
        $escapedProductName = $this->escaper->escapeHtml($product->getName());
        $this->performAddToWishListRequest(['product' => $product->getId()]);
        $this->assertSuccess(1, 1, $escapedProductName);
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
        $this->prepareReferer();
        $this->customerSession->setCustomerId(1);
        $product = $this->productRepository->get('Configurable product');
        $this->performAddToWishListRequest(['product' => $product->getId()]);
        $this->assertSuccess(1, 1, $product->getName());
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
     * Assert success response and items count.
     *
     * @param int $customerId
     * @param int $itemsCount
     * @param string $productName
     * @return void
     */
    private function assertSuccess(int $customerId, int $itemsCount, string $productName): void
    {
        $expectedMessage = sprintf("\n%s has been added to your Wish List.", $productName)
            . " Click <a href=\"http://localhost/test\">here</a> to continue shopping.";
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_SUCCESS);
        $wishlist = $this->getWishlistByCustomerId->execute($customerId);
        $this->assertCount($itemsCount, $wishlist->getItemCollection());
        $this->assertRedirect($this->stringContains('wishlist/index/index/wishlist_id/' . $wishlist->getId()));
    }

    /**
     * Prepare referer to test.
     *
     * @return void
     */
    private function prepareReferer(): void
    {
        $parameters = $this->_objectManager->create(Parameters::class);
        $parameters->set('HTTP_REFERER', 'http://localhost/test');
        $this->getRequest()->setServer($parameters);
    }
}
