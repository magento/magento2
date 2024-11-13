<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\VersionControl;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RelationCompositeTest extends TestCase
{
    /**
     * @var RelationComposite
     */
    protected $entityRelationComposite;

    /**
     * @var AbstractModel|MockObject
     */
    protected $modelMock;

    /**
     * @var RelationInterface
     */
    protected $relationProcessorMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    protected function setUp(): void
    {
        $this->modelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getEventPrefix'
                ]
            )
            ->getMockForAbstractClass();
        $this->relationProcessorMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->relationProcessorMock = $this->getMockBuilder(
            RelationInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->entityRelationComposite = new RelationComposite(
            $this->eventManagerMock,
            [
                'default' => $this->relationProcessorMock
            ]
        );
    }

    public function testProcessRelations()
    {
        $this->relationProcessorMock->expects($this->once())
            ->method('processRelation')
            ->with($this->modelMock);
        $this->modelMock->expects($this->once())
            ->method('getEventPrefix')
            ->willReturn('custom_event_prefix');
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'custom_event_prefix_process_relation',
                [
                    'object' => $this->modelMock
                ]
            );
        $this->entityRelationComposite->processRelations($this->modelMock);
    }
}
