<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Guest;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Helper\Guest;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for guest reorder controller.
 *
 * @see \Magento\Sales\Controller\Guest\Reorder
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class ReorderTest extends AbstractController
{
    /** @var CheckoutSession */
    private $checkoutSession;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var CookieManagerInterface */
    private $cookieManager;

    /** @var Session */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->checkoutSession = $this->_objectManager->get(CheckoutSession::class);
        $this->orderFactory = $this->_objectManager->get(OrderInterfaceFactory::class);
        $this->cookieManager = $this->_objectManager->get(CookieManagerInterface::class);
        $this->customerSession = $this->_objectManager->get(Session::class);
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
     * @magentoDataFixture Magento/Sales/_files/order_by_guest_with_simple_product.php
     *
     * @return void
     */
    public function testReorderSimpleProduct(): void
    {
        $orderIncrementId = 'test_order_1';
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        $cookieValue = base64_encode($order->getProtectCode() . ':' . $orderIncrementId);
        $this->cookieManager->setPublicCookie(Guest::COOKIE_NAME, $cookieValue);
        $this->dispatchReorderRequest();
        $this->assertRedirect($this->stringContains('checkout/cart'));
        $quoteItemsCollection = $this->checkoutSession->getQuote()->getItemsCollection();
        $this->assertCount(1, $quoteItemsCollection);
        $this->assertEquals(
            $order->getItemsCollection()->getFirstItem()->getSku(),
            $quoteItemsCollection->getFirstItem()->getSku()
        );
    }

    /**
     * @return void
     */
    public function testReorderWithoutParamsAndCookie(): void
    {
        $this->dispatchReorderRequest();
        $this->assertRedirect($this->stringContains('sales/guest/form'));
        $this->assertSessionMessages(
            $this->containsEqual((string)__('You entered incorrect data. Please try again.')),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testReorderGuestOrderByCustomer(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->dispatchReorderRequest();
        $this->assertRedirect($this->stringContains('sales/order/history'));
    }

    /**
     * Dispatch reorder request.
     *
     * @return void
     */
    private function dispatchReorderRequest(): void
    {
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->dispatch('sales/guest/reorder/');
    }
}
