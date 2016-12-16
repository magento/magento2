<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiClient;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class tests interaction with Signifyd Case creation service
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CaseCreationServiceTest extends \PHPUnit_Framework_TestCase
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
     * @var ZendClient|MockObject
     */
    private $client;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var CaseCreationService
     */
    private $service;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->client = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHeaders', 'setRawData', 'setMethod', 'setUri', 'request', 'getLastRequest'])
            ->getMock();

        $clientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $clientFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->client);

        $apiClient = $this->objectManager->create(
            ApiClient::class,
            ['clientFactory' => $clientFactory]
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
            CaseCreationService::class,
            [
                'signifydGateway' => $gateway,
                'logger' => $this->logger
            ]
        );
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseCreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCreateForOrderWithEmptyResponse()
    {
        $order = $this->getOrder();
        $requestData = [
            'purchase' => [
                'orderId' => $order->getEntityId()
            ]
        ];

        $response = new \Zend_Http_Response(200, []);
        $this->client->expects(static::once())
            ->method('request')
            ->willReturn($response);
        $this->client->expects(static::atLeastOnce())
            ->method('getLastRequest')
            ->willReturn(json_encode($requestData));

        $this->logger->expects(static::once())
            ->method('error')
            ->with('Unable to process Signifyd API: Response is not valid JSON: Decoding failed: Syntax error');

        $result = $this->service->createForOrder($order->getEntityId());
        static::assertTrue($result);
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseCreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCreateForOrderWithBadResponse()
    {
        $order = $this->getOrder();
        $requestData = [
            'purchase' => [
                'orderId' => $order->getEntityId()
            ]
        ];
        $responseData = [
            'messages' => [
                'Something wrong'
            ]
        ];

        $response = new \Zend_Http_Response(400, [], json_encode($responseData));
        $this->client->expects(static::once())
            ->method('request')
            ->willReturn($response);
        $this->client->expects(static::atLeastOnce())
            ->method('getLastRequest')
            ->willReturn(json_encode($requestData));

        $this->logger->expects(static::once())
            ->method('error')
            ->with(
                'Unable to process Signifyd API: Bad Request - The request could not be parsed. Response: ' .
                json_encode($responseData)
            );

        $result = $this->service->createForOrder($order->getEntityId());
        static::assertTrue($result);
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseCreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCreateOrderWithEmptyInvestigationId()
    {
        $order = $this->getOrder();

        $response = new \Zend_Http_Response(200, [], '{}');
        $this->client->expects(static::once())
            ->method('request')
            ->willReturn($response);

        $this->logger->expects(static::once())
            ->method('error')
            ->with('Expected field "investigationId" missed.');

        $result = $this->service->createForOrder($order->getEntityId());
        static::assertTrue($result);
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseCreationService::createForOrder
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCreateForOrder()
    {
        $order = $this->getOrder();

        $response = new \Zend_Http_Response(200, [], json_encode(['investigationId' => 123123]));
        $this->client->expects(static::once())
            ->method('request')
            ->willReturn($response);

        $this->logger->expects(static::never())
            ->method('error');

        $result = $this->service->createForOrder($order->getEntityId());
        static::assertTrue($result);
    }

    /**
     * Get stored order
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
}
