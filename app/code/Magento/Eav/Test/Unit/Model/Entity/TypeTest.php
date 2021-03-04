<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity;

class TypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attrFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attrSetFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $universalFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(\Magento\Framework\Model\Context::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->attrFactoryMock = $this->createMock(\Magento\Eav\Model\Entity\AttributeFactory::class);
        $this->attrSetFactoryMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\SetFactory::class);
        $this->storeFactoryMock = $this->createPartialMock(\Magento\Eav\Model\Entity\StoreFactory::class, ['create']);
        $this->universalFactoryMock = $this->createMock(\Magento\Framework\Validator\UniversalFactory::class);
        $this->resourceMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            false,
            true,
            ['beginTransaction', 'rollBack', 'commit', 'getIdFieldName', '__wakeup']
        );

        $this->model = new \Magento\Eav\Model\Entity\Type(
            $this->contextMock,
            $this->registryMock,
            $this->attrFactoryMock,
            $this->attrSetFactoryMock,
            $this->storeFactoryMock,
            $this->universalFactoryMock,
            $this->resourceMock
        );
    }

    /**
     */
    public function testFetchNewIncrementIdRollsBackTransactionAndRethrowsExceptionIfProgramFlowIsInterrupted()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Store instance cannot be created.');

        $this->model->setIncrementModel('\IncrementModel');
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        // Interrupt program flow by exception
        $exception = new \Exception('Store instance cannot be created.');
        $this->storeFactoryMock->expects($this->once())->method('create')->will($this->throwException($exception));
        $this->resourceMock->expects($this->once())->method('rollBack');
        $this->resourceMock->expects($this->never())->method('commit');

        $this->model->fetchNewIncrementId();
    }
}
