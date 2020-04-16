<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model;

class CatalogRuleRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\CatalogRuleRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $ruleResourceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $ruleFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $ruleMock;

    protected function setUp(): void
    {
        $this->ruleResourceMock = $this->createMock(\Magento\CatalogRule\Model\ResourceModel\Rule::class);
        $this->ruleFactoryMock = $this->createPartialMock(\Magento\CatalogRule\Model\RuleFactory::class, ['create']);
        $this->ruleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
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
        $ruleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
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
     */
    public function testEnableSaveRule()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('The "1" rule was unable to be saved. Please try again.');

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
        $this->assertTrue($this->repository->delete($this->ruleMock));
    }

    public function testDeleteRuleById()
    {
        $ruleId = 1;
        $ruleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn($ruleId);
        $ruleMock->expects($this->once())->method('load')->with($ruleId)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getId')->willReturn($ruleId);
        $this->ruleResourceMock
            ->expects($this->once())
            ->method('delete')
            ->with($ruleMock);
        $this->assertTrue($this->repository->deleteById($ruleId));
    }

    /**
     */
    public function testUnableDeleteRule()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotDeleteException::class);
        $this->expectExceptionMessage('The "1" rule couldn\'t be removed.');

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
        $ruleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('load')->with($ruleId)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn($ruleId);
        $this->assertEquals($ruleMock, $this->repository->get($ruleId));
        /** verify that rule was cached */
        $this->assertEquals($ruleMock, $this->repository->get($ruleId));
    }

    /**
     */
    public function testGetNonExistentRule()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('The rule with the "1" ID wasn\'t found. Verify the ID and try again.');

        $ruleId = 1;
        $ruleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('load')->with($ruleId)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn(null);
        $this->assertEquals($ruleMock, $this->repository->get($ruleId));
    }
}
