<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Plugin\CustomerRepository;

class TransactionWrapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Plugin\CustomerRepository\TransactionWrapper
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Customer\Model\ResourceModel\Customer
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Customer\Api\CustomerRepositoryInterface
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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerMock;

    /**
     * @var string
     */
    protected $passwordHash = true;

    const ERROR_MSG = "error occurred";

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(\Magento\Customer\Model\ResourceModel\Customer::class);
        $this->subjectMock = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customerMock = $this->customerMock;
        $this->closureMock = function () use ($customerMock) {
            return $customerMock;
        };
        $this->rollbackClosureMock = function () use ($customerMock) {
            throw new \Exception(self::ERROR_MSG);
        };

        $this->model = new \Magento\Customer\Model\Plugin\CustomerRepository\TransactionWrapper($this->resourceMock);
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

    /**
     */
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
