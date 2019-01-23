<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller;

use Magento\Checkout\Model\Session;
use Magento\Paypal\Model\Config;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * @magentoDataFixture Magento/Sales/_files/order.php
 */
class PayflowTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->_objectManager->get(FilterBuilder::class);
        $filters = [
            $filterBuilder->setField(OrderInterface::INCREMENT_ID)
                ->setValue('100000001')
                ->create(),
        ];

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilters($filters)
            ->create();

        $this->orderRepository = $this->_objectManager->get(OrderRepositoryInterface::class);
        $orders = $this->orderRepository->getList($searchCriteria)
            ->getItems();

        /** @var OrderInterface $order */
        $this->order = array_pop($orders);
        $this->order->getPayment()->setMethod(Config::METHOD_PAYFLOWLINK);

        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->_objectManager->create(Quote::class)->setStoreid($this->order->getStoreId());

        $this->quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $this->quoteRepository->save($quote);

        $this->order->setQuoteId($quote->getId());
        $this->orderRepository->save($this->order);

        $session = $this->_objectManager->get(Session::class);
        $session->setLastRealOrderId($this->order->getRealOrderId())->setLastQuoteId($this->order->getQuoteId());
    }

    public function testCancelPaymentActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/cancelpayment');
        $this->assertContains("goToSuccessPage = ''", $this->getResponse()->getBody());
    }

    public function testReturnurlActionIsContentGenerated()
    {
        $checkoutHelper = $this->_objectManager->create(\Magento\Paypal\Helper\Checkout::class);
        $checkoutHelper->cancelCurrentOrder('test');
        $this->dispatch('paypal/payflow/returnurl');
        $this->assertContains("goToSuccessPage = ''", $this->getResponse()->getBody());
    }

    public function testFormActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/form');
        $this->assertContains(
            '<form id="token_form" method="GET" action="https://payflowlink.paypal.com">',
            $this->getResponse()->getBody()
        );
        // Check P3P header
        $headerConstraints = [];
        foreach ($this->getResponse()->getHeaders() as $header) {
            $headerConstraints[] = new \PHPUnit\Framework\Constraint\IsEqual($header->getFieldName());
        }
        $constraint = new \PHPUnit\Framework\Constraint\LogicalOr();
        $constraint->setConstraints($headerConstraints);
        $this->assertThat('P3P', $constraint);
    }

    /**
     * @magentoConfigFixture current_store payment/paypal_payflow/active 1
     * @magentoConfigFixture current_store paypal/general/business_account merchant_2012050718_biz@example.com
     * @return void
     */
    public function testCancelAction(): void
    {
        $orderId = $this->order->getEntityId();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->quoteRepository->get($order->getQuoteId());

        $session = $this->_objectManager->get(Session::class);
        $session->setQuoteId($quote->getId());
        $session->setPaypalStandardQuoteId($quote->getId())->setLastRealOrderId('100000001');
        $this->dispatch('paypal/payflow/cancelpayment');

        $order = $this->_objectManager->create(OrderRepositoryInterface::class)->get($orderId);
        $this->assertEquals('canceled', $order->getState());
        $this->assertEquals($session->getQuote()->getGrandTotal(), $quote->getGrandTotal());
        $this->assertEquals($session->getQuote()->getItemsCount(), $quote->getItemsCount());
    }
}
