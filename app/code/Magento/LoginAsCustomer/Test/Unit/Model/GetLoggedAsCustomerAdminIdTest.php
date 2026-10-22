<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Test\Unit\Model;

use Magento\Customer\Model\Session;
use Magento\LoginAsCustomer\Model\GetLoggedAsCustomerAdminId;
use PHPUnit\Framework\TestCase;

class GetLoggedAsCustomerAdminIdTest extends TestCase
{
    /**
     * @var Session&\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionMock;

    /**
     * @var GetLoggedAsCustomerAdminId
     */
    private GetLoggedAsCustomerAdminId $model;

    protected function setUp(): void
    {
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getLoggedAsCustomerAdminId', 'getLoggedAsCustomerAdmindId'])
            ->getMock();
        $this->model = new GetLoggedAsCustomerAdminId($this->sessionMock);
    }

    public function testExecuteUsesCorrectedSessionGetter(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('getLoggedAsCustomerAdminId')
            ->willReturn('123');

        $this->sessionMock->expects($this->never())
            ->method('getLoggedAsCustomerAdmindId');

        $this->assertSame(123, $this->model->execute());
    }

    public function testExecuteFallsBackToLegacySessionGetter(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('getLoggedAsCustomerAdminId')
            ->willReturn(null);

        $this->sessionMock->expects($this->once())
            ->method('getLoggedAsCustomerAdmindId')
            ->willReturn('456');

        $this->assertSame(456, $this->model->execute());
    }
}
