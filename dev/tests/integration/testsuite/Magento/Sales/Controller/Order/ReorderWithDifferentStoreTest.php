<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for reorder with different store.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class ReorderWithDifferentStoreTest extends AbstractController
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

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $currentStoreId;

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
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->currentStoreId = $this->storeManager->getStore()->getId();
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
        $this->storeManager->setCurrentStore($this->currentStoreId);
        parent::tearDown();
    }

    /**
     * Test when reorder from different store and global customer account
     *
     * @magentoConfßigFixture web/url/use_store 1
     * @magentoConfigFixture customer/account_share/scope 0
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_simple_product.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testReorderWithDifferentStoreAndGlobalCustomerAccount(): void
    {
        $this->checkoutSession->resetCheckout();
        $this->storeManager->setCurrentStore('fixture_second_store');
        $order = $this->orderFactory->create()->loadByIncrementId('55555555');
        $orderNumber = (int) $order->getId();
        $this->customerSession->setCustomerId($order->getCustomerId());
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setParam('order_id', $orderNumber);
        $this->dispatch('sales/order/reorder/');
        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->quote = $this->checkoutSession->getQuote();

        $this->assertCount(1, $this->quote->getErrors());
    }
}
