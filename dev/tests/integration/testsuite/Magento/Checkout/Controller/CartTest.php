<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Checkout\Controller\Cart
 */
namespace Magento\Checkout\Controller;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Request;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as OrderItemCollection;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation enabled
 */
class CartTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /** @var CheckoutSession */
    private $checkoutSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->checkoutSession = $this->_objectManager->get(CheckoutSession::class);
        $this->_objectManager->addSharedInstance($this->checkoutSession, CheckoutSession::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(CheckoutSession::class);
        parent::tearDown();
    }

    /**
     * Test for \Magento\Checkout\Controller\Cart::configureAction() with simple product
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product.php
     */
    public function testConfigureActionWithSimpleProduct()
    {
        /** @var $session CheckoutSession */
        $session = $this->_objectManager->create(CheckoutSession::class);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('simple');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product');

        $this->dispatch(
            'checkout/cart/configure/id/' . $quoteItem->getId() . '/product_id/' . $quoteItem->getProduct()->getId()
        );
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//button[@type="submit" and @title="Update Cart"]',
                $response->getBody()
            ),
            'Response for simple product doesn\'t contain "Update Cart" button'
        );
    }

    /**
     * Test for \Magento\Checkout\Controller\Cart::configureAction() with simple product and custom option
     *
     * @magentoDataFixture Magento/Checkout/_files/cart_with_simple_product_and_custom_options.php
     */
    public function testConfigureActionWithSimpleProductAndCustomOption()
    {
        /** @var Quote $quote */
        $quote = $this->getQuote('test_order_item_with_custom_options');
        $this->checkoutSession->setQuoteId($quote->getId());

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('simple_with_custom_options');

        $quoteItem = $this->_getQuoteItemIdByProductId($quote, $product->getId());
        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product with custom option');

        $this->dispatch(
            'checkout/cart/configure/id/' . $quoteItem->getId() . '/product_id/' . $quoteItem->getProduct()->getId()
        );
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//button[@type="submit" and @title="Update Cart"]',
                $response->getBody()
            ),
            'Response for simple product with custom option doesn\'t contain "Update Cart" button'
        );

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//input[contains(@class,"product-custom-option") and @type="text"]',
                $response->getBody()
            ),
            'Response for simple product with custom option doesn\'t contain custom option input field'
        );
    }

    /**
     * Test for \Magento\Checkout\Controller\Cart::configureAction() with bundle product
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_bundle_product.php
     * @magentoDbIsolation disabled
     */
    public function testConfigureActionWithBundleProduct()
    {
        /** @var $session CheckoutSession */
        $session = $this->_objectManager->create(CheckoutSession::class);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('bundle-product');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $this->assertNotNull($quoteItem, 'Cannot get quote item for bundle product');

        $this->dispatch(
            'checkout/cart/configure/id/' . $quoteItem->getId() . '/product_id/' . $quoteItem->getProduct()->getId()
        );
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//button[@type="submit" and @title="Update Cart"]',
                $response->getBody()
            ),
            'Response for bundle product doesn\'t contain "Update Cart" button'
        );
    }

    /**
     * Test for \Magento\Checkout\Controller\Cart::configureAction() with downloadable product
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_downloadable_product.php
     */
    public function testConfigureActionWithDownloadableProduct()
    {
        /** @var $session CheckoutSession */
        $session = $this->_objectManager->create(CheckoutSession::class);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('downloadable-product');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $this->assertNotNull($quoteItem, 'Cannot get quote item for downloadable product');

        $this->dispatch(
            'checkout/cart/configure/id/' . $quoteItem->getId() . '/product_id/' . $quoteItem->getProduct()->getId()
        );
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//button[@type="submit" and @title="Update Cart"]',
                $response->getBody()
            ),
            'Response for downloadable product doesn\'t contain "Update Cart" button'
        );

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="downloadable-links-list"]',
                $response->getBody()
            ),
            'Response for downloadable product doesn\'t contain links for download'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product.php
     * @magentoAppIsolation enabled
     */
    public function testUpdatePostAction()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('simple');

        /** Preconditions */
        $customerFromFixture = 1;
        $productId = $product->getId();
        $originalQuantity = 1;
        $updatedQuantity = 2;
        /** @var $checkoutSession CheckoutSession */
        $checkoutSession = $this->_objectManager->create(CheckoutSession::class);
        $quoteItem = $this->_getQuoteItemIdByProductId($checkoutSession->getQuote(), $productId);

        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $postData = [
            'cart' => [$quoteItem->getId() => ['qty' => $updatedQuantity]],
            'update_cart_action' => 'update_qty',
            'form_key' => $formKey->getFormKey(),
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        /** @var $customerSession \Magento\Customer\Model\Session */
        $customerSession = $this->_objectManager->create(\Magento\Customer\Model\Session::class);
        $customerSession->setCustomerId($customerFromFixture);

        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product');
        $this->assertEquals(
            $originalQuantity,
            $quoteItem->getQty(),
            "Precondition failed: invalid quote item quantity"
        );

        /** Execute SUT */
        $this->dispatch('checkout/cart/updatePost');

        /** Check results */
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load($checkoutSession->getQuote()->getId());
        $quoteItem = $this->_getQuoteItemIdByProductId($quote, $product->getId());
        $this->assertEquals($updatedQuantity, $quoteItem->getQty(), "Invalid quote item quantity");
    }

    /**
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote($reservedOrderId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }

    /**
     * Gets \Magento\Quote\Model\Quote\Item from \Magento\Quote\Model\Quote by product id
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param string|int $productId
     *
     * @return \Magento\Quote\Model\Quote\Item|null
     */
    private function _getQuoteItemIdByProductId($quote, $productId)
    {
        /** @var $quoteItems \Magento\Quote\Model\Quote\Item[] */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($productId == $quoteItem->getProductId()) {
                return $quoteItem;
            }
        }
        return null;
    }

    /**
     * Test for \Magento\Checkout\Controller\Cart::execute() with simple product
     *
     * @param string $area
     * @param string $expectedPrice
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     * @dataProvider addAddProductDataProvider
     */
    public function testAddToCartSimpleProduct($area, $expectedPrice)
    {
        $formKey = $this->_objectManager->get(FormKey::class);
        $postData = [
            'qty' => '1',
            'product' => '1',
            'custom_price' => 1,
            'form_key' => $formKey->getFormKey(),
            'isAjax' => 1
        ];
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);

        $quote =  $this->_objectManager->create(\Magento\Checkout\Model\Cart::class);
        /** @var \Magento\Checkout\Controller\Cart\Add $controller */
        $controller = $this->_objectManager->create(\Magento\Checkout\Controller\Cart\Add::class, [$quote]);
        $controller->execute();

        $this->assertStringContainsString(json_encode([]), $this->getResponse()->getBody());
        $items = $quote->getItems()->getItems();
        $this->assertIsArray($items, 'Quote doesn\'t have any items');
        $this->assertCount(1, $items, 'Expected quote items not equal to 1');
        $item = reset($items);
        $this->assertEquals(1, $item->getProductId(), 'Quote has more than one product');
        $this->assertEquals($expectedPrice, $item->getPrice(), 'Expected product price failed');
    }

    /**
     * Data provider for testAddToCartSimpleProduct
     */
    public function addAddProductDataProvider()
    {
        return [
            'frontend' => ['frontend', 'expected_price' => 10],
            'adminhtml' => ['adminhtml', 'expected_price' => 1]
        ];
    }

    /**
     * @covers \Magento\Checkout\Controller\Cart\Addgroup::execute()
     *
     * Test customer can add items to cart only if they belong to him.
     *
     * @param bool $loggedIn
     * @param string $request
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Checkout/_files/order_items.php
     * @dataProvider reorderItemsDataProvider
     * @return void
     */
    public function testReorderItems(bool $loggedIn, string $request)
    {
        // Make sure test starts without logged in customer.
        $customerSession = $this->_objectManager->get(CustomerSession::class);
        $customerSession->logout();

        $checkoutSession = Bootstrap::getObjectManager()->get(Session::class);
        $expected = [];
        if ($loggedIn && $request == Request::METHOD_POST) {
            $customer = $this->_objectManager->create(CustomerRepository::class)->get('customer2@example.com');
            $customerSession->setCustomerDataObject($customer);
            $orderCollection = $this->_objectManager->create(OrderCollection::class);
            $orderCollection->addFieldToFilter('customer_id', $customer->getId());
            $orderItemCollection = $this->_objectManager->create(OrderItemCollection::class);
            $orderItemCollection->addFieldToFilter('order_id', ['in' => $orderCollection->getAllIds()]);
            $expected = $orderItemCollection->getColumnValues('product_id');
        }
        $this->prepareRequest($request);
        $this->dispatch('checkout/cart/addGroup');

        $this->assertEquals(
            $expected,
            $checkoutSession->getQuote()->getItemsCollection()->getColumnValues('product_id')
        );

        // Make sure test doesn't left logged in customer after execution.
        $customerSession->logout();
    }

    /**
     * Data provider for testReorderItems.
     *
     * @return array
     */
    public function reorderItemsDataProvider()
    {
        return [
            [
                'logged_in' => false,
                'request_type' => Request::METHOD_POST,
            ],
            [
                'logged_in' => false,
                'request_type' => Request::METHOD_GET,
            ],
            [
                'logged_in' => true,
                'request_type' => Request::METHOD_POST,
            ],
            [
                'logged_in' => true,
                'request_type' => Request::METHOD_GET,
            ],
        ];
    }

    /**
     * Prepare request for testReorderItems.
     *
     * @param string $method
     * @return void
     */
    private function prepareRequest(string $method)
    {
        /** @var OrderItemCollection $orderItems */
        $orderItems = $this->_objectManager->create(OrderItemCollection::class);
        /** @var FormKey $key */
        $key = $this->_objectManager->get(FormKey::class);
        $data = [
            'form_key' => $key->getFormKey(),
            'order_items' => $orderItems->getAllIds(),
        ];
        $this->getRequest()->setMethod($method);
        switch ($method) {
            case Request::METHOD_POST:
                $this->getRequest()->setPostValue($data);
                break;
            case Request::METHOD_GET:
            default:
                $this->getRequest()->setParams($data);
                break;
        }
    }
}
