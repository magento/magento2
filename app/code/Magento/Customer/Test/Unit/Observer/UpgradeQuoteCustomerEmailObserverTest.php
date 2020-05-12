<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Observer\UpgradeQuoteCustomerEmailObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

/** for testing upgrade quote customer email
 */
class UpgradeQuoteCustomerEmailObserverTest extends TestCase
{
    /**
     * @var UpgradeQuoteCustomerEmailObserver
     */
    protected $model;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepositoryMock;

    /**
     * @var Observer
     */
    protected $observerMock;

    /**
     * @var Event
     */
    protected $eventMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerDataObject', 'getOrigCustomerDataObject'])
            ->getMock();

        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($this->eventMock);

        $this->quoteRepositoryMock = $this
            ->getMockBuilder(CartRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->model = new UpgradeQuoteCustomerEmailObserver($this->quoteRepositoryMock);
    }

    /**
     * Unit test for verifying quote customers email upgrade observer
     */
    public function testUpgradeQuoteCustomerEmail()
    {
        $email = "test@test.com";
        $origEmail = "origtest@test.com";

        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerOrig = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->setMethods(['setCustomerEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock->expects($this->any())
            ->method('getCustomerDataObject')
            ->willReturn($customer);
        $this->eventMock->expects($this->any())
            ->method('getOrigCustomerDataObject')
            ->willReturn($customerOrig);

        $customerOrig->expects($this->any())
            ->method('getEmail')
            ->willReturn($this->returnValue($origEmail));

        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn($this->returnValue($email));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getForCustomer')
            ->willReturn($quoteMock);

        $quoteMock->expects($this->once())
            ->method('setCustomerEmail');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock);

        $this->model->execute($this->observerMock);
    }
}
