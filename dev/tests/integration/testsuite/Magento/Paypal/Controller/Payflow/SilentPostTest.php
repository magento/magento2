<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflow;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Logger\Monolog;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Paypal\Model\Payflowlink;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * @magentoAppIsolation enabled
 */
class SilentPostTest extends AbstractController
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
     * Checks a test case when Payflow Link callback action receives Silent Post notification with transaction details.
     *
     * @param int $resultCode
     * @param string $orderState
     * @param string $orderStatus
     * @magentoDataFixture Magento/Paypal/_files/order_payflow_link.php
     * @dataProvider responseCodeDataProvider
     */
    public function testSuccessfulNotification($resultCode, $orderState, $orderStatus)
    {
        $orderIncrementId = '000000045';
        $this->withRequest($orderIncrementId, $resultCode);
        $this->withGatewayResponse($orderIncrementId, $resultCode);

        $this->dispatch('paypal/payflow/silentPost');

        self::assertEquals(200, $this->getResponse()->getStatusCode());

        $order = $this->getOrder($orderIncrementId);
        self::assertEquals($orderState, $order->getState());
        self::assertEquals($orderStatus, $order->getStatus());
    }

    /**
     * Get list of different variations for Silent Post action testing,
     * like different response codes from PayPal.
     *
     * @return array
     */
    public function responseCodeDataProvider()
    {
        return [
            [Payflowlink::RESPONSE_CODE_APPROVED, Order::STATE_COMPLETE, Order::STATE_COMPLETE],
            [Payflowlink::RESPONSE_CODE_FRAUDSERVICE_FILTER, Order::STATE_PAYMENT_REVIEW, Order::STATUS_FRAUD],
        ];
    }

    /**
     * Checks a test case when Payflow Link callback receives Silent Post notification from PayPal
     * with fraudulent transaction and PayPal gateway configured to reject this kind of transactions.
     *
     * @magentoDataFixture Magento/Paypal/_files/order_payflow_link.php
     */
    public function testFraudulentNotification()
    {
        $orderIncrementId = '000000045';
        $resultCode = Payflowlink::RESPONSE_CODE_DECLINED_BY_FILTER;
        $this->withRequest($orderIncrementId, $resultCode);
        $this->withGatewayResponse($orderIncrementId, $resultCode);

        $logger = $this->getMockBuilder(Monolog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager->addSharedInstance($logger, LoggerInterface::class, true);

        $exception = new CommandException(__('Response message from PayPal gateway'));
        $logger->expects(self::once())
            ->method('critical')
            ->with(self::equalTo($exception));

        $this->dispatch('paypal/payflow/silentPost');

        self::assertEquals(200, $this->getResponse()->getStatusCode());

        $this->_objectManager->removeSharedInstance(LoggerInterface::class, true);
    }

    /**
     * Imitates real POST request with test data.
     *
     * @param string $orderIncrementId
     * @param int $resultCode
     * @return void
     */
    private function withRequest($orderIncrementId, $resultCode)
    {
        $data = [
            'INVNUM' => $orderIncrementId,
            'AMT' => 100,
            'PNREF' => 'A21CP234KLB8',
            'USER2' => 'cf7i85d01ed7c92223031afb4rdl2f1f',
            'RESULT' => $resultCode,
            'TYPE' => 'A',
        ];
        $this->getRequest()->setPostValue($data);
    }

    /**
     * Imitates response from PayPal gateway.
     *
     * @param string $orderIncrementId
     * @param int $resultCode
     * @return void
     */
    private function withGatewayResponse($orderIncrementId, $resultCode)
    {
        $response = new DataObject([
            'custref' => $orderIncrementId,
            'origresult' => $resultCode,
            'respmsg' => 'Response message from PayPal gateway'
        ]);
        $this->gateway->method('postRequest')
            ->willReturn($response);
    }

    /**
     * Gets order stored by fixture.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder($incrementId)
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->_objectManager->get(FilterBuilder::class);
        $filters = [
            $filterBuilder->setField(OrderInterface::INCREMENT_ID)
                ->setValue($incrementId)
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
