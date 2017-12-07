<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\QuoteRepository\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Model\QuoteRepository\Plugin\AccessChangeQuoteControl;
use Magento\Quote\Model\Quote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\QuoteRepository;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class AccessChangeQuoteControlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AccessChangeQuoteControl
     */
    private $accessChangeQuoteControl;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var QuoteRepository|MockObject
     */
    private $quoteRepositoryMock;

    protected function setUp()
    {
        $this->userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->getMockForAbstractClass();
        $this->userContextMock->method('getUserId')
            ->willReturn(1);

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
            ->getMock();

        $this->quoteRepositoryMock = $this->getMockBuilder(QuoteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->accessChangeQuoteControl = $objectManagerHelper->getObject(
            AccessChangeQuoteControl::class,
            ['userContext' => $this->userContextMock]
        );
    }

    /**
     * User with role Customer and customer_id much with context user_id.
     */
    public function testBeforeSaveForCustomer()
    {
        $this->quoteMock->method('getCustomerId')
            ->willReturn(1);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $result = $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);

        $this->assertNull($result);
    }

    /**
     * The user_id and customer_id from the quote are different.
     *
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Invalid state change requested
     */
    public function testBeforeSaveException()
    {
        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->quoteMock->method('getCustomerId')
            ->willReturn(2);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }

    /**
     * User with role Admin and customer_id not much with user_id.
     */
    public function testBeforeSaveForAdmin()
    {
        $this->quoteMock->method('getCustomerId')
            ->willReturn(2);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);

        $result = $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);

        $this->assertNull($result);
    }

    /**
     * User with role Guest and customer_id === null.
     */
    public function testBeforeSaveForGuest()
    {
        $this->quoteMock->method('getCustomerId')
            ->willReturn(null);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);

        $result = $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);

        $this->assertNull($result);
    }

    /**
     * User with role Guest and customer_id !== null.
     *
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Invalid state change requested
     */
    public function testBeforeSaveForGuestException()
    {
        $this->quoteMock->method('getCustomerId')
            ->willReturn(1);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }

    /**
     * User with unknown role.
     *
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Invalid state change requested
     */
    public function testBeforeSaveForUnknownUserTypeException()
    {
        $this->quoteMock->method('getCustomerId')
            ->willReturn(2);

        $this->userContextMock->method('getUserType')
            ->willReturn(10);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }
}
