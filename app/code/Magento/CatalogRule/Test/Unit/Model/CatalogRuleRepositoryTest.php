<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Model;

use Magento\CatalogRule\Model\CatalogRuleRepository;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\RuleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogRuleRepositoryTest extends TestCase
{
    /**
     * @var CatalogRuleRepository
     */
    protected $repository;

    /**
     * @var MockObject
     */
    protected $ruleResourceMock;

    /**
     * @var MockObject
     */
    protected $ruleFactoryMock;

    /**
     * @var MockObject
     */
    protected $ruleMock;

    protected function setUp(): void
    {
        $this->ruleResourceMock = $this->createMock(Rule::class);
        $this->ruleFactoryMock = $this->createPartialMock(RuleFactory::class, ['create']);
        $this->ruleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
        $this->repository = new CatalogRuleRepository(
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

    public function testEnableSaveRule()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
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

    public function testUnableDeleteRule()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
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

    public function testGetNonExistentRule()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The rule with the "1" ID wasn\'t found. Verify the ID and try again.');
        $ruleId = 1;
        $ruleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('load')->with($ruleId)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn(null);
        $this->assertEquals($ruleMock, $this->repository->get($ruleId));
    }
}
