<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ForgotPasswordToken;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken;
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
     * @var CustomerResource|MockObject
     */
    private $customerResourceMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerResourceMock = $this->createMock(CustomerResource::class);

        $getCustomerByTokenMock = $this->createMock(GetCustomerByToken::class);
        $getCustomerByTokenMock->method('execute')->willReturn($this->customerMock);

        $this->model = new ConfirmCustomerByToken($getCustomerByTokenMock, $this->customerResourceMock);
    }

    /**
     * Confirm customer with confirmation
     *
     * @return void
     */
    public function testExecuteWithConfirmation(): void
    {
        $customerId = 777;

        $this->customerMock->expects($this->once())
            ->method('getConfirmation')
            ->willReturn('GWz2ik7Kts517MXAgrm4DzfcxKayGCm4');
        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $this->customerResourceMock->expects($this->once())
            ->method('updateColumn')
            ->with($customerId, 'confirmation', null);

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
        $this->customerResourceMock->expects($this->never())
            ->method('updateColumn');

        $this->model->execute(self::STUB_RESET_PASSWORD_TOKEN);
    }
}
