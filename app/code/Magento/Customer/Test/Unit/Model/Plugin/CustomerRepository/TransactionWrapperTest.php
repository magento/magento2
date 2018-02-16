<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Plugin\CustomerRepository;

class TransactionWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Plugin\CustomerRepository\TransactionWrapper
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\ResourceModel\Customer
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\CustomerRepositoryInterface
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var string
     */
    protected $passwordHash = true;

    const ERROR_MSG = "error occurred";

    protected function setUp()
    {
        $this->resourceMock = $this->getMock('Magento\Customer\Model\ResourceModel\Customer', [], [], '', false);
        $this->subjectMock = $this->getMock('Magento\Customer\Api\CustomerRepositoryInterface', [], [], '', false);
        $this->customerMock = $this->getMock('Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
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
     * @expectedException \Exception
     * @expectedExceptionMessage error occurred
     */
    public function testAroundSaveRollBack()
    {
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
