<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken.
 */
class ConfirmCustomerByTokenTest extends TestCase
{
    private const STUB_RESET_PASSWORD_TOKEN = 'resetPassword';

    /**
     * @var ConfirmCustomerByToken;
     */
    private $model;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setData'])
            ->getMockForAbstractClass();

        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);

        $getCustomerByTokenMock = $this->createMock(GetCustomerByToken::class);
        $getCustomerByTokenMock->method('execute')->willReturn($this->customerMock);

        $this->model = new ConfirmCustomerByToken($getCustomerByTokenMock, $this->customerRepositoryMock);
    }

    /**
     * Confirm customer with confirmation
     *
     * @return void
     */
    public function testExecuteWithConfirmation(): void
    {
        $this->customerMock->expects($this->once())
            ->method('getConfirmation')
            ->willReturn('GWz2ik7Kts517MXAgrm4DzfcxKayGCm4');
        $this->customerMock->expects($this->once())
            ->method('setData')
            ->with('ignore_validation_flag', true);
        $this->customerMock->expects($this->once())
            ->method('setConfirmation')
            ->with(null);
        $this->customerRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->customerMock);

        $this->model->execute(self::STUB_RESET_PASSWORD_TOKEN);
    }

    /**
     * Confirm customer without confirmation
     *
     * @return void
     */
    public function testExecuteWithoutConfirmation(): void
    {
        $this->customerMock->expects($this->once())
            ->method('getConfirmation')
            ->willReturn(null);
        $this->customerRepositoryMock->expects($this->never())
            ->method('save');

        $this->model->execute(self::STUB_RESET_PASSWORD_TOKEN);
    }
}
