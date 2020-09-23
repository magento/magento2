<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity;

use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\Entity\StoreFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Framework\Validator\UniversalFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    /**
     * @var Type
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $registryMock;

    /**
     * @var MockObject
     */
    protected $attrFactoryMock;

    /**
     * @var MockObject
     */
    protected $attrSetFactoryMock;

    /**
     * @var MockObject
     */
    protected $storeFactoryMock;

    /**
     * @var MockObject
     */
    protected $universalFactoryMock;

    /**
     * @var MockObject
     */
    protected $resourceMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->attrFactoryMock = $this->createMock(AttributeFactory::class);
        $this->attrSetFactoryMock = $this->createMock(SetFactory::class);
        $this->storeFactoryMock = $this->createPartialMock(StoreFactory::class, ['create']);
        $this->universalFactoryMock = $this->createMock(UniversalFactory::class);
        $this->resourceMock = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            false,
            true,
            ['beginTransaction', 'rollBack', 'commit', 'getIdFieldName', '__wakeup']
        );

        $this->model = new Type(
            $this->contextMock,
            $this->registryMock,
            $this->attrFactoryMock,
            $this->attrSetFactoryMock,
            $this->storeFactoryMock,
            $this->universalFactoryMock,
            $this->resourceMock
        );
    }

    public function testFetchNewIncrementIdRollsBackTransactionAndRethrowsExceptionIfProgramFlowIsInterrupted()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Store instance cannot be created.');
        $this->model->setIncrementModel('\IncrementModel');
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        // Interrupt program flow by exception
        $exception = new \Exception('Store instance cannot be created.');
        $this->storeFactoryMock->expects($this->once())->method('create')->willThrowException($exception);
        $this->resourceMock->expects($this->once())->method('rollBack');
        $this->resourceMock->expects($this->never())->method('commit');

        $this->model->fetchNewIncrementId();
    }
}
