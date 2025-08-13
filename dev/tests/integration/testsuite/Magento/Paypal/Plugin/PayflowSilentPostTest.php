<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Plugin;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Paypal\Model\Payflowlink;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @magentoAppIsolation enabled
 */
class PayflowSilentPostTest extends AbstractController
{
    /**
     * @var Gateway|MockObject
     */
    private $gateway;

    /**
     * @var OrderSender|MockObject
     */
    private $orderSender;

    /**
     * @var string
     */
    protected $orderIncrementId = '000000045';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderSender = $this->getMockBuilder(OrderSender::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_objectManager->addSharedInstance($this->gateway, Gateway::class);
        $this->_objectManager->addSharedInstance($this->orderSender, OrderSender::class);

        $order = $this->getOrder();
        $payment = $this->_objectManager->create(Payment::class);
        $payment->setMethod(Config::METHOD_PAYFLOWLINK)
            ->setBaseAmountAuthorized(100)
            ->setAdditionalInformation(
                [
                    'secure_silent_post_hash' => 'cf7i85d01ed7c92223031afb4rdl2f1f'
                ]
            );
        $order->setPayment($payment);
        $order->setState(Order::STATE_PENDING_PAYMENT)
            ->setStatus(Order::STATE_PENDING_PAYMENT);
        $orderRepository = $this->_objectManager->get(OrderRepositoryInterface::class);
        $orderRepository->save($order);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(Gateway::class);
        $this->_objectManager->removeSharedInstance(OrderSender::class);
        parent::tearDown();
    }

    /**
     * Checks a test case when Payflow Link return url before plugin is executed with transaction details.
     *
     * @param int $resultCode
     * @param string $orderState
     * @param string $orderStatus
     * @magentoDataFixture Magento/Paypal/_files/order_payflow_link.php
     * @dataProvider responseCodeDataProvider
     */
    public function testOrderStatusWithDifferentPaypalResponse($resultCode, $orderState, $orderStatus)
    {
        $this->withRequest($resultCode);
        $this->withGatewayResponse($resultCode);

        $this->dispatch('paypal/payflow/returnUrl');
        self::assertEquals(200, $this->getResponse()->getStatusCode());

        $order = $this->getOrder();
        self::assertEquals($orderState, $order->getState());
        self::assertEquals($orderStatus, $order->getStatus());
    }

    /**
     * Get list of different variations for paypal response
     *
     * @return array
     */
    public static function responseCodeDataProvider()
    {
        return [
            [Payflowlink::RESPONSE_CODE_APPROVED, Order::STATE_COMPLETE, Order::STATE_COMPLETE],
            [Payflowlink::RESPONSE_CODE_DECLINED, Order::STATE_PENDING_PAYMENT, Order::STATE_PENDING_PAYMENT]
        ];
    }

    /**
     * Imitates real request with test data.
     *
     * @param int $resultCode
     * @return void
     */
    private function withRequest($resultCode)
    {
        $data = [
            'INVNUM' => $this->orderIncrementId,
            'AMT' => 100,
            'PNREF' => 'A21CP234KLB8',
            'USER2' => 'cf7i85d01ed7c92223031afb4rdl2f1f',
            'RESULT' => $resultCode,
            'TYPE' => 'A',
            'RESPMSG' => 'Approved'
        ];
        $this->getRequest()->setParams($data);
    }

    /**
     * Imitates response from PayPal gateway.
     *
     * @param int $resultCode
     * @return void
     */
    private function withGatewayResponse($resultCode)
    {
        $response = new DataObject([
            'custref' => $this->orderIncrementId,
            'origresult' => $resultCode,
            'respmsg' => 'Response message from PayPal gateway'
        ]);
        $this->gateway->method('postRequest')
            ->willReturn($response);
    }

    /**
     * Gets order stored by fixture.
     *
     * @return OrderInterface
     */
    private function getOrder()
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->_objectManager->get(FilterBuilder::class);
        $filters = [
            $filterBuilder->setField(OrderInterface::INCREMENT_ID)
                ->setValue($this->orderIncrementId)
                ->create()
        ];

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilters($filters)
            ->create();

        $orderRepository = $this->_objectManager->get(OrderRepositoryInterface::class);
        $orders = $orderRepository->getList($searchCriteria)
            ->getItems();

        /** @var OrderInterface $order */
        return array_pop($orders);
    }
}
