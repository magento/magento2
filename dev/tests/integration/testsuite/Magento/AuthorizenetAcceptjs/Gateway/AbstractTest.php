<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Model\Quote\PaymentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Zend_Http_Response;

abstract class AbstractTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ZendClient|MockObject|InvocationMocker
     */
    protected $clientMock;

    /**
     * @var PaymentFactory
     */
    protected $paymentFactory;

    /**
     * @var Zend_Http_Response
     */
    protected $responseMock;

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setUp()
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->loadArea(Area::AREA_FRONTEND);
        $this->objectManager = Bootstrap::getObjectManager();
        $this->clientMock = $this->createMock(ZendClient::class);
        $this->responseMock = $this->createMock(Zend_Http_Response::class);
        $this->clientMock->method('request')
            ->willReturn($this->responseMock);
        $this->clientMock->method('setUri')
            ->with('https://apitest.authorize.net/xml/v1/request.api');
        $clientFactoryMock = $this->createMock(ZendClientFactory::class);
        $clientFactoryMock->method('create')
            ->willReturn($this->clientMock);
        /** @var PaymentDataObjectFactory $paymentFactory */
        $this->paymentFactory = $this->objectManager->get(PaymentDataObjectFactory::class);
        $this->objectManager->addSharedInstance($clientFactoryMock, ZendClientFactory::class);
    }

    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(ZendClientFactory::class);
        parent::tearDown();
    }

    protected function getOrderWithIncrementId(string $incrementId): Order
    {
        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $searchCriteria = $this->objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter('increment_id', $incrementId)
            ->create();
        /** @var Order $order */
        $order = current(
            $orderRepository->getList($searchCriteria)
                ->getItems()
        );

        return $order;
    }
}
