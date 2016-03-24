<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model;

class CatalogRuleRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\CatalogRuleRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleMock;

    protected function setUp()
    {
        $this->ruleResourceMock = $this->getMock('Magento\CatalogRule\Model\ResourceModel\Rule', [], [], '', false);
        $this->ruleFactoryMock = $this->getMock('Magento\CatalogRule\Model\RuleFactory', ['create'], [], '', false);
        $this->ruleMock = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $this->repository = new \Magento\CatalogRule\Model\CatalogRuleRepository(
            $this->ruleResourceMock,
            $this->ruleFactoryMock
        );
    }

    public function testSave()
    {
        $this->ruleMock->expects($this->once())->method('getRuleId')->willReturn(null);
        $this->ruleMock->expects($this->once())->method('getId')->willReturn(1);
        $this->ruleResourceMock->expects($this->once())->method('save')->with($this->ruleMock);
        $this->assertEquals($this->ruleMock, $this->repository->save($this->ruleMock));
    }

    public function testEditRule()
    {
        $ruleId = 1;
        $ruleData = ['id' => $ruleId];
        $this->ruleMock->expects($this->once())->method('getData')->willReturn($ruleData);
        $ruleMock = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $this->ruleMock->expects($this->exactly(2))->method('getRuleId')->willReturn($ruleId);
        $ruleMock->expects($this->once())->method('addData')->with($ruleData)->willReturn($ruleMock);
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('load')->with($ruleId)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn($ruleId);
        $this->ruleResourceMock->expects($this->once())->method('save')->with($ruleMock)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getId')->willReturn($ruleId);
        $this->assertEquals($ruleMock, $this->repository->save($this->ruleMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to save rule 1
     */
    public function testEnableSaveRule()
    {
        $this->ruleMock->expects($this->at(0))->method('getRuleId')->willReturn(null);
        $this->ruleMock->expects($this->at(1))->method('getRuleId')->willReturn(1);
        $this->ruleMock->expects($this->never())->method('getId');
        $this->ruleResourceMock
            ->expects($this->once())
            ->method('save')
            ->with($this->ruleMock)->willThrowException(new \Exception());
        $this->repository->save($this->ruleMock);
    }

    public function testDeleteRule()
    {
        $this->ruleMock->expects($this->once())->method('getId')->willReturn(1);
        $this->ruleResourceMock
            ->expects($this->once())
            ->method('delete')
            ->with($this->ruleMock);
        $this->assertEquals(true, $this->repository->delete($this->ruleMock));
    }

    public function testDeleteRuleById()
    {
        $ruleId = 1;
        $ruleMock = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn($ruleId);
        $ruleMock->expects($this->once())->method('load')->with($ruleId)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getId')->willReturn($ruleId);
        $this->ruleResourceMock
            ->expects($this->once())
            ->method('delete')
            ->with($ruleMock);
        $this->assertEquals(true, $this->repository->deleteById($ruleId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Unable to remove rule 1
     */
    public function testUnableDeleteRule()
    {
        $this->ruleMock->expects($this->once())->method('getRuleId')->willReturn(1);
        $this->ruleResourceMock
            ->expects($this->once())
            ->method('delete')
            ->with($this->ruleMock)->willThrowException(new \Exception());
        $this->repository->delete($this->ruleMock);
    }

    public function testGetRule()
    {
        $ruleId = 1;
        $ruleMock = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('load')->with($ruleId)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn($ruleId);
        $this->assertEquals($ruleMock, $this->repository->get($ruleId));
        /** verify that rule was cached */
        $this->assertEquals($ruleMock, $this->repository->get($ruleId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Rule with specified ID "1" not found.
     */
    public function testGetNonExistentRule()
    {
        $ruleId = 1;
        $ruleMock = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('load')->with($ruleId)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn(null);
        $this->assertEquals($ruleMock, $this->repository->get($ruleId));
    }
}
