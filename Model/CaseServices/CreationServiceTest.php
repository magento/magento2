<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiCallException;
use Magento\Signifyd\Model\SignifydGateway\ApiClient;
use Magento\Signifyd\Model\SignifydGateway\Client\RequestBuilder;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class tests interaction with Signifyd Case creation service
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreationServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var RequestBuilder|MockObject
     */
    private $requestBuilder;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var CreationService
     */
    private $service;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->requestBuilder = $this->getMockBuilder(RequestBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['doRequest'])
            ->getMock();

        $apiClient = $this->objectManager->create(
            ApiClient::class,
            ['requestBuilder' => $this->requestBuilder]
        );

        $gateway = $this->objectManager->create(
            Gateway::class,
            ['apiClient' => $apiClient]
        );

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['error'])
            ->getMockForAbstractClass();

        $this->service = $this->objectManager->create(
            CreationService::class,
            [
                'signifydGateway' => $gateway,
                'logger'          => $this->logger
            ]
        );
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseServices\CreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCreateForOrderWithEmptyResponse()
    {
        $order = $this->getOrder();
        $exceptionMessage = 'Response is not valid JSON: Decoding failed: Syntax error';

        $this->requestBuilder->expects(static::once())
            ->method('doRequest')
            ->willThrowException(new ApiCallException($exceptionMessage));

        $this->logger->expects(static::once())
            ->method('error')
            ->with($exceptionMessage);

        $result = $this->service->createForOrder($order->getEntityId());
        static::assertTrue($result);
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseServices\CreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCreateForOrderWithBadResponse()
    {
        $order = $this->getOrder();
        $responseData = [
            'messages' => [
                'Something wrong'
            ]
        ];
        $exceptionMessage = 'Bad Request - The request could not be parsed. Response: ' . json_encode($responseData);

        $this->requestBuilder->expects(static::once())
            ->method('doRequest')
            ->willThrowException(new ApiCallException($exceptionMessage));

        $this->logger->expects(static::once())
            ->method('error')
            ->with($exceptionMessage);

        $result = $this->service->createForOrder($order->getEntityId());
        static::assertTrue($result);
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseServices\CreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCreateOrderWithEmptyInvestigationId()
    {
        $order = $this->getOrder();

        $this->requestBuilder->expects(static::once())
            ->method('doRequest')
            ->willReturn([]);

        $this->logger->expects(static::once())
            ->method('error')
            ->with('Expected field "investigationId" missed.');

        $result = $this->service->createForOrder($order->getEntityId());
        static::assertTrue($result);
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseServices\CreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCreateForOrder()
    {
        $order = $this->getOrder();

        $this->requestBuilder->expects(static::once())
            ->method('doRequest')
            ->willReturn(['investigationId' => 123123]);

        $this->logger->expects(static::never())
            ->method('error');

        $result = $this->service->createForOrder($order->getEntityId());
        static::assertTrue($result);

        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        $caseEntity = $caseRepository->getByCaseId(123123);
        $gridGuarantyStatus = $this->getOrderGridGuarantyStatus($caseEntity->getOrderId());

        static::assertNotEmpty($caseEntity);
        static::assertEquals($order->getEntityId(), $caseEntity->getOrderId());
        static::assertEquals(
            $gridGuarantyStatus,
            $caseEntity->getGuaranteeDisposition(),
            'Signifyd guaranty status in sales_order_grid table does not match case entity guaranty status'
        );

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($caseEntity->getOrderId());
        static::assertEquals(Order::STATE_HOLDED, $order->getState());

        $histories = $order->getStatusHistories();
        static::assertNotEmpty($histories);

        /** @var OrderStatusHistoryInterface $orderHoldComment */
        $orderHoldComment = array_pop($histories);
        static::assertInstanceOf(OrderStatusHistoryInterface::class, $orderHoldComment);
        static::assertEquals("Awaiting the Signifyd guarantee disposition.", $orderHoldComment->getComment());
    }

    /**
     * Get stored order
     *
     * @return OrderInterface
     */
    private function getOrder()
    {
        if ($this->order === null) {
            /** @var FilterBuilder $filterBuilder */
            $filterBuilder = $this->objectManager->get(FilterBuilder::class);
            $filters = [
                $filterBuilder->setField(OrderInterface::INCREMENT_ID)
                    ->setValue('100000001')
                    ->create()
            ];

            /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
            $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
            $searchCriteria = $searchCriteriaBuilder->addFilters($filters)
                ->create();

            $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
            $orders = $orderRepository->getList($searchCriteria)
                ->getItems();

            $this->order = array_pop($orders);
        }

        return $this->order;
    }

    /**
     * Returns value of signifyd_guarantee_status column from sales order grid
     *
     * @param int $orderEntityId
     * @return string|null
     */
    private function getOrderGridGuarantyStatus($orderEntityId)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $orderGridCollection */
        $orderGridCollection = $this->objectManager->get(
            \Magento\Sales\Model\ResourceModel\Order\Grid\Collection::class
        );

        $items = $orderGridCollection->addFilter($orderGridCollection->getIdFieldName(), $orderEntityId)
            ->getItems();
        $result = array_pop($items);

        return isset($result['signifyd_guarantee_status']) ? $result['signifyd_guarantee_status'] : null;
    }
}
