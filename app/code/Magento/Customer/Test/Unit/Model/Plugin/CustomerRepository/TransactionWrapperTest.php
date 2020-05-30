<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Plugin\CustomerRepository;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Plugin\CustomerRepository\TransactionWrapper;
use Magento\Customer\Model\ResourceModel\Customer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionWrapperTest extends TestCase
{
    /**
     * @var TransactionWrapper
     */
    protected $model;

    /**
     * @var MockObject|Customer
     */
    protected $resourceMock;

    /**
     * @var MockObject|CustomerRepositoryInterface
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \Closure
     */
    protected $rollbackClosureMock;

    /**
     * @var MockObject
     */
    protected $customerMock;

    /**
     * @var string
     */
    protected $passwordHash = true;

    const ERROR_MSG = "error occurred";

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(Customer::class);
        $this->subjectMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock = $this->customerMock;
        $this->closureMock = function () use ($customerMock) {
            return $customerMock;
        };
        $this->rollbackClosureMock = function () use ($customerMock) {
            throw new \Exception(self::ERROR_MSG);
        };

        $this->model = new TransactionWrapper($this->resourceMock);
    }

    public function testAroundSaveCommit()
    {
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('commit');

        $this->assertEquals(
            $this->customerMock,
            $this->model->aroundSave($this->subjectMock, $this->closureMock, $this->customerMock, $this->passwordHash)
        );
    }

    public function testAroundSaveRollBack()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('error occurred');

        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('rollBack');

        $this->model->aroundSave(
            $this->subjectMock,
            $this->rollbackClosureMock,
            $this->customerMock,
            $this->passwordHash
        );
    }
}
