<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\QuoteRepository\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Model\ChangeQuoteControl;
use Magento\Quote\Model\QuoteRepository\Plugin\AccessChangeQuoteControl;
use Magento\Quote\Model\Quote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\QuoteRepository;
use \PHPUnit\Framework\MockObject\MockObject as MockObject;

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

    /**
     * @var ChangeQuoteControl|MockObject
     */
    private $changeQuoteControlMock;

    protected function setUp(): void
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

        $this->changeQuoteControlMock = $this->getMockBuilder(ChangeQuoteControl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->accessChangeQuoteControl = $objectManagerHelper->getObject(
            AccessChangeQuoteControl::class,
            ['changeQuoteControl' => $this->changeQuoteControlMock]
        );
    }

    /**
     * User with role Customer and customer_id matches context user_id.
     */
    public function testBeforeSaveForCustomer()
    {
        $this->quoteMock->method('getCustomerId')
            ->willReturn(1);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(true);

        $result = $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);

        $this->assertNull($result);
    }

    /**
     * The user_id and customer_id from the quote are different.
     *
     */
    public function testBeforeSaveException()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('Invalid state change requested');

        $this->quoteMock->method('getCustomerId')
            ->willReturn(2);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(false);

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

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(true);

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

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(true);

        $result = $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);

        $this->assertNull($result);
    }

    /**
     * User with role Guest and customer_id !== null.
     *
     */
    public function testBeforeSaveForGuestException()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('Invalid state change requested');

        $this->quoteMock->method('getCustomerId')
            ->willReturn(1);

        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(false);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }

    /**
     * User with unknown role.
     *
     */
    public function testBeforeSaveForUnknownUserTypeException()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('Invalid state change requested');

        $this->quoteMock->method('getCustomerId')
            ->willReturn(2);

        $this->userContextMock->method('getUserType')
            ->willReturn(10);

        $this->changeQuoteControlMock->method('isAllowed')
            ->willReturn(false);

        $this->accessChangeQuoteControl->beforeSave($this->quoteRepositoryMock, $this->quoteMock);
    }
}
