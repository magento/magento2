<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Test\Unit\Model;

use Magento\Customer\Model\Session;
use Magento\LoginAsCustomer\Model\SetLoggedAsCustomerAdminId;
use PHPUnit\Framework\TestCase;

class SetLoggedAsCustomerAdminIdTest extends TestCase
{
    /**
     * @var Session
     */
    private Session $sessionMock;

    /**
     * @var SetLoggedAsCustomerAdminId
     */
    private SetLoggedAsCustomerAdminId $model;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->model = new SetLoggedAsCustomerAdminId($this->sessionMock);
    }

    public function testExecuteWritesBothCorrectAndLegacySessionKeys(): void
    {
        $adminId = 789;

        $this->sessionMock->expects($this->once())
            ->method('setLoggedAsCustomerAdminId')
            ->with($adminId);

        $this->sessionMock->expects($this->once())
            ->method('setLoggedAsCustomerAdmindId')
            ->with($adminId);

        $this->model->execute($adminId);
    }
}
