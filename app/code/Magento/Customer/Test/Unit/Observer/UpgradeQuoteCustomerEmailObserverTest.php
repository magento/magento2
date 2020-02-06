<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Observer\UpgradeQuoteCustomerEmailObserver;

/**
 * Class UpgradeQuoteCustomerEmailObserverTest for testing upgrade quote customer email
 */
class UpgradeQuoteCustomerEmailObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpgradeQuoteCustomerEmailObserver
     */
    protected $model;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepositoryMock;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $observerMock;

    /**
     * @var \Magento\Framework\Event
     */
    protected $eventMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerDataObject', 'getOrigCustomerDataObject'])
            ->getMock();

        $this->observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->eventMock));

        $this->quoteRepositoryMock = $this
            ->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
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

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerOrig = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods(['setCustomerEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock->expects($this->any())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customer));
        $this->eventMock->expects($this->any())
            ->method('getOrigCustomerDataObject')
            ->will($this->returnValue($customerOrig));

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
