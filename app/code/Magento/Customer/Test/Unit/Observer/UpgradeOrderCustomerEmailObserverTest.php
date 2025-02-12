<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Observer\UpgradeOrderCustomerEmailObserver;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * For testing upgrade order customer email
 */
class UpgradeOrderCustomerEmailObserverTest extends TestCase
{
    private const NEW_CUSTOMER_EMAIL = "test@test.com";
    private const ORIGINAL_CUSTOMER_EMAIL = "origtest@test.com";

    /**
     * @var UpgradeOrderCustomerEmailObserver
     */
    private $orderCustomerEmailObserver;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMock();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerDataObject', 'getOrigCustomerDataObject'])
            ->getMock();

        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($this->eventMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->orderCustomerEmailObserver = $this->objectManagerHelper->getObject(
            UpgradeOrderCustomerEmailObserver::class,
            [
                'orderRepository' => $this->orderRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
            ]
        );
    }

    /**
     * Verifying that the order email is not updated when the customer email is not updated
     *
     */
    public function testUpgradeOrderCustomerEmailWhenMailIsNotChanged(): void
    {
        $customer = $this->createCustomerMock();
        $originalCustomer = $this->createCustomerMock();

        $this->setCustomerToEventMock($customer);
        $this->setOriginalCustomerToEventMock($originalCustomer);

        $this->setCustomerEmail($originalCustomer, self::ORIGINAL_CUSTOMER_EMAIL);
        $this->setCustomerEmail($customer, self::ORIGINAL_CUSTOMER_EMAIL);

        $this->whenOrderRepositoryGetListIsNotCalled();

        $this->orderCustomerEmailObserver->execute($this->observerMock);
    }

    /**
     * Verifying that the order email is updated after the customer updates their email
     *
     */
    public function testUpgradeOrderCustomerEmail(): void
    {
        $customer = $this->createCustomerMock();
        $originalCustomer = $this->createCustomerMock();
        $orderCollectionMock = $this->createOrderMock();

        $this->setCustomerToEventMock($customer);
        $this->setOriginalCustomerToEventMock($originalCustomer);

        $this->setCustomerEmail($originalCustomer, self::ORIGINAL_CUSTOMER_EMAIL);
        $this->setCustomerEmail($customer, self::NEW_CUSTOMER_EMAIL);

        $this->whenOrderRepositoryGetListIsCalled($orderCollectionMock);

        $this->whenOrderCollectionSetDataToAllIsCalled($orderCollectionMock);

        $this->whenOrderCollectionSaveIsCalled($orderCollectionMock);

        $this->orderCustomerEmailObserver->execute($this->observerMock);
    }

    private function createCustomerMock(): MockObject
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $customer;
    }

    private function createOrderMock(): MockObject
    {
        $orderCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $orderCollectionMock;
    }

    private function setCustomerToEventMock(MockObject $customer): void
    {
        $this->eventMock->expects($this->once())
            ->method('getCustomerDataObject')
            ->willReturn($customer);
    }

    private function setOriginalCustomerToEventMock(MockObject $originalCustomer): void
    {
        $this->eventMock->expects($this->once())
            ->method('getOrigCustomerDataObject')
            ->willReturn($originalCustomer);
    }

    private function setCustomerEmail(MockObject $originalCustomer, string $email): void
    {
        $originalCustomer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($email);
    }

    private function whenOrderRepositoryGetListIsCalled(MockObject $orderCollectionMock): void
    {
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->willReturn($this->searchCriteriaBuilderMock);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($orderCollectionMock);
    }

    private function whenOrderCollectionSetDataToAllIsCalled(MockObject $orderCollectionMock): void
    {
        $orderCollectionMock->expects($this->once())
            ->method('setDataToAll')
            ->with(OrderInterface::CUSTOMER_EMAIL, self::NEW_CUSTOMER_EMAIL);
    }

    private function whenOrderCollectionSaveIsCalled(MockObject $orderCollectionMock): void
    {
        $orderCollectionMock->expects($this->once())
            ->method('save');
    }

    private function whenOrderRepositoryGetListIsNotCalled(): void
    {
        $this->searchCriteriaBuilderMock->expects($this->never())
            ->method('addFilter');
        $this->searchCriteriaBuilderMock->expects($this->never())
            ->method('create');

        $this->orderRepositoryMock->expects($this->never())
            ->method('getList');
    }
}
