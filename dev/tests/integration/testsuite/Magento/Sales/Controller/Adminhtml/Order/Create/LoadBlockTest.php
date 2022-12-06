<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Wishlist\Model\Wishlist;

/**
 * Class checks create order load block controller.
 *
 * @see \Magento\Sales\Controller\Adminhtml\Order\Create\LoadBlock
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoadBlockTest extends AbstractBackendController
{
    /** @var LayoutInterface */
    private $layout;

    /** @var GetQuoteByReservedOrderId */
    private $getQuoteByReservedOrderId;

    /** @var Quote */
    private $session;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var array */
    private $quoteIdsToRemove;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->layout = $this->_objectManager->get(LayoutInterface::class);
        $this->getQuoteByReservedOrderId = $this->_objectManager->get(GetQuoteByReservedOrderId::class);
        $this->session = $this->_objectManager->get(Quote::class);
        $this->quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->quoteIdsToRemove[] = $this->session->getQuote()->getId();
        foreach ($this->quoteIdsToRemove as $quoteId) {
            try {
                $this->quoteRepository->delete($this->quoteRepository->get($quoteId));
            } catch (NoSuchEntityException $e) {
                //do nothing
            }
        }

        $this->session->clearStorage();

        parent::tearDown();
    }

    /**
     * @dataProvider responseFlagsProvider
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_customer_without_address.php
     *
     * @param bool $asJson
     * @param bool $asJsVarname
     * @return void
     */
    public function testAddProductToOrderFromShoppingCart(bool $asJson, bool $asJsVarname): void
    {
        $oldQuote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $params = $this->hydrateParams([
            'json' => $asJson,
            'as_js_varname' => $asJsVarname,
        ]);
        $itemId = $oldQuote->getItemsCollection()->getFirstItem()->getId();
        $post = $this->hydratePost([
            'sidebar' => [
                'add_cart_item' => [
                    $itemId => 1,
                ],
            ],
        ]);

        $this->dispatchWitParams($params, $post);

        $this->checkHandles(explode(',', $params['block']), $asJson);

        $newQuote = $this->session->getQuote();
        $newQuoteItemsCollection = $newQuote->getItemsCollection(false);
        $this->assertNotNull($newQuoteItemsCollection->getItemByColumnValue('sku', 'simple2'));
        if ($asJsVarname) {
            $this->assertRedirect($this->stringContains('sales/order_create/showUpdateResult'));
            $body = (string) $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getUpdateResult();
        } elseif ($asJson) {
            $body = json_decode($this->getResponse()->getBody(), true, 512, JSON_THROW_ON_ERROR)['sidebar'];
        } else {
            $body = $this->getResponse()->getBody();
        }
        $this->assertStringNotContainsString("sidebar[add_cart_item][$itemId]", $body);
    }

    /**
     * @return array
     */
    public function responseFlagsProvider(): array
    {
        return [
            'as_json' => [
                'as_json' => true,
                'as_js_varname' => false,
            ],
            'as_plain' => [
                'as_json' => false,
                'as_js_varname' => true,
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_customer_without_address.php
     *
     * @return void
     */
    public function testRemoveProductFromShoppingCart(): void
    {
        $oldQuote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $post = $this->hydratePost([
            'sidebar' => [
                'remove' => [
                    $oldQuote->getItemsCollection()->getFirstItem()->getId() => 'cart',
                ],
            ],
        ]);
        $params = $this->hydrateParams();

        $this->dispatchWitParams($params, $post);

        $this->checkHandles(explode(',', $params['block']));
        $this->checkQuotes($oldQuote);
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_customer_without_address.php
     *
     * @return void
     */
    public function testClearShoppingCart(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $post = $this->hydratePost([
            'sidebar' => [
                'empty_customer_cart' => '1',
            ],
        ]);
        $params = $this->hydrateParams();

        $this->dispatchWitParams($params, $post);

        $this->checkHandles(explode(',', $params['block']));
        $this->assertEmpty($quote->getItemsCollection(false)->getItems());
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/inactive_quote_with_customer.php
     *
     * @return void
     */
    public function testMoveFromOrderToShoppingCart(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_inactive_quote');
        $this->session->setQuoteId($quote->getId());
        $post = $this->hydratePost([
            'update_items' => '1',
            'item' => [
                $quote->getItemsCollection()->getFirstItem()->getId() => [
                    'qty' => '1',
                    'use_discount' => '1',
                    'action' => 'cart',
                ],
            ],
        ]);
        $params = $this->hydrateParams(['blocks' => null]);
        $this->dispatchWitParams($params, $post);
        $customerCart = $this->quoteRepository->getForCustomer(1);
        $cartItems = $customerCart->getItemsCollection();
        $this->assertCount(1, $cartItems->getItems());
        $this->assertEquals('taxable_product', $cartItems->getFirstItem()->getSku());
        $this->quoteIdsToRemove[] = $customerCart->getId();
    }

    /**
     * Check that Wishlist item is deleted after it has been added to Order.
     *
     * @return void
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     * @magentoDbIsolation disabled
     */
    public function testAddProductToOrderFromWishList(): void
    {
        /** @var Wishlist $wishlist */
        $wishlist = $this->_objectManager->create(Wishlist::class);
        $wishlistItems = $wishlist->loadByCustomerId(1)->getItemCollection();
        $this->assertCount(1, $wishlistItems);
        $itemId = $wishlistItems->getFirstItem()->getId();

        $post = $this->hydratePost([
            'sidebar' => [
                'add_wishlist_item' => [
                    $itemId => 1,
                ],
            ],
        ]);
        $params = $this->hydrateParams([
            'json' => false,
            'as_js_varname' => false,
        ]);
        $this->dispatchWitParams($params, $post);

        $body = $this->getResponse()->getBody();
        $this->assertStringNotContainsString("sidebar[add_wishlist_item][$itemId]", $body);
        $quoteItems = $this->session->getQuote()->getItemsCollection();
        $this->assertCount(1, $quoteItems);
    }

    /**
     * Check that customer notification is NOT disabled after comment is updated.
     *
     * @return void
     * @magentoDataFixture Magento/Checkout/_files/quote_with_customer_without_address.php
     */
    public function testUpdateCustomerNote(): void
    {
        $customerNote = 'Example Comment';
        $quoteId = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address')->getId();
        $this->session->setQuoteId($quoteId);
        $params = [
            'json' => false,
            'block' => 'totals',
            'as_js_varname' => false,
        ];
        $post = $this->hydratePost([
            'order' => [
                'comment' => [
                    CartInterface::KEY_CUSTOMER_NOTE => $customerNote
                ],
            ],
        ]);
        $this->dispatchWitParams($params, $post);

        $quote = $this->session->getQuote();
        $this->assertEquals($customerNote, $quote->getCustomerNote());
        $this->assertTrue((bool)$quote->getCustomerNoteNotify());

        preg_match('/id="notify_customer"(?<attributes>.*?)\/>/s', $this->getResponse()->getBody(), $matches);
        $this->assertArrayHasKey('attributes', $matches);
        $this->assertStringContainsString('checked="checked"', $matches['attributes']);
    }

    /**
     * Check that specific store id is setting into the current stores.
     *
     * @return void
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @throws NoSuchEntityException
     */
    public function testSetSpecificStoreIdIntoCurrentStore()
    {
        $params = [];
        $post = ['store_id' => $this->storeManager->getStore('fixture_second_store')->getId()];
        $this->dispatchWitParams($params, $post);
        $this->assertEquals('fixture_second_store', $this->storeManager->getStore()->getCode());
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testThatItemsTransferredFromShoppingCartAreDeletedAfterOrderIsCreated(): void
    {
        $oldQuote = $this->getQuoteByReservedOrderId->execute('test_order_1');
        $this->assertNotEmpty($oldQuote->getItemsCollection(false)->getItems());
        $itemId = $oldQuote->getItemsCollection()->getFirstItem()->getId();
        $params = $this->hydrateParams();
        $post = $this->hydratePost([
            'sidebar' => [
                'add_cart_item' => [
                    $itemId => 1,
                ],
            ],
        ]);

        $this->dispatchWitParams($params, $post);
        $this->assertNotEmpty($oldQuote->getItemsCollection(false)->getItems());
        $this->placeOrder();
        $this->assertEmpty($oldQuote->getItemsCollection(false)->getItems());
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testThatItemsTransferredFromWishlistAreDeletedAfterOrderIsCreated(): void
    {
        /** @var Wishlist $wishlist */
        $wishlist = $this->_objectManager->create(Wishlist::class);
        $wishlistItems = $wishlist->loadByCustomerId(1)->getItemCollection();
        $this->assertCount(1, $wishlistItems);
        $itemId = $wishlistItems->getFirstItem()->getId();

        $post = $this->hydratePost([
            'sidebar' => [
                'add_wishlist_item' => [
                    $itemId => 1,
                ],
            ],
        ]);
        $params = $this->hydrateParams();
        $this->dispatchWitParams($params, $post);
        $wishlist = $this->_objectManager->create(Wishlist::class);
        $wishlistItems = $wishlist->loadByCustomerId(1)->getItemCollection();
        $this->assertCount(1, $wishlistItems);
        $this->placeOrder();
        $wishlist = $this->_objectManager->create(Wishlist::class);
        $wishlistItems = $wishlist->loadByCustomerId(1)->getItemCollection();
        $this->assertCount(0, $wishlistItems);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function placeOrder(): void
    {
        $this->_request = null;
        $this->_response = null;
        Bootstrap::getInstance()->getBootstrap()->getApplication()->reinitialize();
        Bootstrap::getInstance()->loadArea('adminhtml');
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->getRequest()
            ->setMethod(\Magento\Framework\App\Request\Http::METHOD_POST)
            ->setPostValue([
                'order' => [
                    'account' => [
                        'email' => 'john.doe001@test.com',
                    ],
                    'shipping_method' => 'flatrate_flatrate',
                    'payment_method' => 'checkmo',
                ],
                'collect_shipping_rates' => true
            ]);
        $this->dispatch('backend/sales/order_create/save');
        $this->assertSessionMessages(
            $this->isEmpty(),
            MessageInterface::TYPE_ERROR
        );
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You created the order.')]),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Check customer quotes
     *
     * @param CartInterface $oldQuote
     * @param string|null $expectedSku
     * @return void
     */
    private function checkQuotes(CartInterface $oldQuote, ?string $expectedSku = null): void
    {
        $newQuote = $this->session->getQuote();
        $oldQuoteItemCollection = $oldQuote->getItemsCollection(false);
        $this->assertEmpty($oldQuoteItemCollection->getItems());
        $newQuoteItemsCollection = $newQuote->getItemsCollection(false);

        if ($expectedSku !== null) {
            $this->assertNotNull($newQuoteItemsCollection->getItemByColumnValue('sku', $expectedSku));
        } else {
            $this->assertEmpty($newQuoteItemsCollection->getItems());
        }
    }

    /**
     * Check that all required handles were applied
     *
     * @param array $blocks
     * @param bool $asJson
     * @return void
     */
    private function checkHandles(array $blocks, bool $asJson = true): void
    {
        $handles = $this->layout->getUpdate()->getHandles();

        if ($asJson) {
            $this->assertContains('sales_order_create_load_block_message', $handles);
            $this->assertContains('sales_order_create_load_block_json', $handles);
        } else {
            $this->assertContains('sales_order_create_load_block_plain', $handles);
        }

        foreach ($blocks as $block) {
            $this->assertContains(
                'sales_order_create_load_block_' . $block,
                $handles
            );
        }
    }

    /**
     * Fill post params array to proper state
     *
     * @param array $inputArray
     * @return array
     */
    private function hydratePost(array $inputArray = []): array
    {
        return array_merge(
            [
                'customer_id' => 1,
                'store_id' => $this->storeManager->getStore('default')->getId(),
                'sidebar' => [],
            ],
            $inputArray
        );
    }

    /**
     * Fill params array to proper state
     *
     * @param array $inputArray
     * @return array
     */
    private function hydrateParams(array $inputArray = []): array
    {
        return array_merge(
            [
                'json' => true,
                'block' => 'sidebar,items,shipping_method,billing_method,totals,giftmessage',
                'as_js_varname' => true,
            ],
            $inputArray
        );
    }

    /**
     * Dispatch request with params
     *
     * @param array $params
     * @param array $postParams
     * @return void
     */
    private function dispatchWitParams(array $params, array $postParams): void
    {
        $this->getRequest()->setMethod(Http::METHOD_POST)
            ->setPostValue($postParams)
            ->setParams($params);
        $this->dispatch('backend/sales/order_create/loadBlock');
    }
}
