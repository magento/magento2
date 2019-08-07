<?php

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

    protected $observerMock;

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
     *
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

        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn($this->returnValue($email));
        $customerOrig->expects($this->any())
            ->method('getEmail')
            ->willReturn($this->returnValue($origEmail));

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