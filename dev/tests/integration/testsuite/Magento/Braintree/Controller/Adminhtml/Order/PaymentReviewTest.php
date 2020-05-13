<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Adminhtml\Order;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Message\MessageInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Class PaymentReviewTest
 */
class PaymentReviewTest extends AbstractBackendController
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->_objectManager->get(FilterBuilder::class);
        $filters = [
            $filterBuilder->setField(OrderInterface::INCREMENT_ID)
                ->setValue('100000002')
                ->create()
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
    }

    /**
     * @covers \Magento\Sales\Controller\Adminhtml\Order\ReviewPayment::execute
     * @magentoDataFixture Magento/Braintree/_files/fraud_order.php
     * @magentoAppArea adminhtml
     */
    public function testExecuteAccept()
    {
        $orderId = $this->order->getEntityId();
        $this->dispatch('backend/sales/order/reviewPayment/action/accept/order_id/' . $orderId);

        static::assertRedirect(static::stringContains('sales/order/view/order_id/' . $orderId));
        static::assertSessionMessages(
            static::equalTo(['The payment has been accepted.']),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->orderRepository->get($orderId);
        static::assertEquals(Order::STATE_COMPLETE, $order->getState());
        static::assertEquals(Order::STATE_COMPLETE, $order->getStatus());
    }

    /**
     * @covers \Magento\Sales\Controller\Adminhtml\Order\ReviewPayment::execute
     * @magentoDataFixture Magento/Braintree/_files/fraud_order.php
     * @magentoAppArea adminhtml
     */
    public function testExecuteDeny()
    {
        $orderId = $this->order->getEntityId();
        $payment = $this->order->getPayment();

        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['denyPayment'])
            ->getMock();
        // uses the mock instead a real adapter to avoid api calls to Braintree gateway
        $payment->setMethodInstance($adapter);
        $this->orderRepository->save($this->order);

        $adapter->expects(static::once())
            ->method('denyPayment')
            ->with($payment)
            ->willReturn(true);

        $this->dispatch('backend/sales/order/reviewPayment/action/deny/order_id/' . $orderId);

        static::assertRedirect(static::stringContains('sales/order/view/order_id/' . $orderId));
        static::assertSessionMessages(
            static::equalTo(['The payment has been denied.']),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->orderRepository->get($orderId);
        static::assertEquals(Order::STATE_CANCELED, $order->getState());
        static::assertEquals(Order::STATE_CANCELED, $order->getStatus());
    }
}
