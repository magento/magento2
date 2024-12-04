<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Core\Version\View;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for reorder controller.
 *
 * @see \Magento\Sales\Controller\Order\Reorder
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class ReorderTest extends AbstractController
{
    /** @var CheckoutSession */
    private $checkoutSession;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var Session */
    private $customerSession;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var CartInterface */
    private $quote;

    /** @var Escaper */
    private $escaper;

    /**
     * @var View
     */
    private $versionChecker;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->checkoutSession = $this->_objectManager->get(CheckoutSession::class);
        $this->orderFactory = $this->_objectManager->get(OrderInterfaceFactory::class);
        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->versionChecker = $this->_objectManager->get(View::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->quote instanceof CartInterface) {
            $this->quoteRepository->delete($this->quote);
        }
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_taxable_product.php
     *
     * @return void
     */
    public function testReorder(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('test_order_with_taxable_product');
        $this->customerSession->setCustomerId($order->getCustomerId());
        $this->dispatchReorderRequest((int)$order->getId());
        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->quote = $this->checkoutSession->getQuote();
        $quoteItemsCollection = $this->quote->getItemsCollection();
        $this->assertCount(1, $quoteItemsCollection);
        $this->assertEquals(
            $order->getItemsCollection()->getFirstItem()->getSku(),
            $quoteItemsCollection->getFirstItem()->getSku()
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_simple_product.php
     *
     * @return void
     */
    public function testReorderProductLowQty(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('55555555');
        $this->customerSession->setCustomerId($order->getCustomerId());
        $this->dispatchReorderRequest((int)$order->getId());
        $this->assertRedirect($this->stringContains('checkout/cart'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_two_items.php
     *
     * @return void
     */
    public function testReorderByAnotherCustomer(): void
    {
        $this->customerSession->setCustomerId(1);
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->dispatchReorderRequest((int)$order->getId());

        if ($this->versionChecker->isVersionUpdated()) {
            $this->assertRedirect($this->stringContains('noroute'));
        } else {
            $this->assertRedirect($this->stringContains('sales/order/history'));
        }
    }

    /**
     * Reorder with JS calendar options
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_js_date_option_product.php
     * @magentoConfigFixture current_store catalog/custom_options/use_calendar 1
     *
     * @return void
     */
    public function testReorderWithJSCalendar(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $items = $order->getItems();
        $orderItem = array_pop($items);
        $orderRequestOptions = $orderItem->getProductOptionByCode('info_buyRequest')['options'];
        $order->save();
        $this->customerSession->setCustomerId($order->getCustomerId());
        $this->dispatchReorderRequest((int)$order->getId());
        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->quote = $this->checkoutSession->getQuote();
        $quoteItemsCollection = $this->quote->getItemsCollection();
        $this->assertCount(1, $quoteItemsCollection);
        $items = $quoteItemsCollection->getItems();
        $quoteItem = array_pop($items);
        $quoteRequestOptions = $quoteItem->getBuyRequest()->getOptions();
        $this->assertEquals($orderRequestOptions, $quoteRequestOptions);
    }

    /**
     * Dispatch reorder request.
     *
     * @param null|int $orderId
     * @return void
     */
    private function dispatchReorderRequest(?int $orderId = null): void
    {
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setParam('order_id', $orderId);
        $this->dispatch('sales/order/reorder/');
    }
}
