<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\ResourceModel\Attribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    /**
     * @var Attribute|MockObject
     */
    protected $attribute;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $appResourceMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var AbstractModel|MockObject
     */
    protected $modelMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->appResourceMock = $this->createMock(ResourceConnection::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->modelMock = $this->getMockForAbstractClass(
            AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getEventPrefix', 'getEventObject']
        );
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['rollback', 'describeTable', 'insert', 'lastInsertId', 'beginTransaction', 'commit'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->willReturn([]);
        $this->connectionMock->expects($this->any())
            ->method('insert');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');
        $this->attribute = new Attribute(
            $this->appResourceMock,
            $this->eventManagerMock
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSave(): void
    {
        $this->appResourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->modelMock->expects($this->any())
            ->method('getEventPrefix')
            ->willReturn('event_prefix');
        $this->modelMock->expects($this->any())
            ->method('getEventObject')
            ->willReturn('event_object');
        $this->eventManagerMock
            ->method('dispatch')
            ->withConsecutive(
                [
                    'event_prefix_save_attribute_before',
                    [
                        'event_object' => $this->attribute,
                        'object' => $this->modelMock,
                        'attribute' => ['attribute']
                    ]
                ],
                [
                    'event_prefix_save_attribute_after',
                    [
                        'event_object' => $this->attribute,
                        'object' => $this->modelMock,
                        'attribute' => ['attribute']
                    ]
                ]
            );
        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');
        $this->connectionMock->expects($this->once())
            ->method('commit');
        $this->assertEquals($this->attribute, $this->attribute->saveAttribute($this->modelMock, 'attribute'));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSaveFailed(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Expected Exception');
        $this->modelMock->expects($this->any())
            ->method('getEventPrefix')
            ->willReturn('event_prefix');
        $this->modelMock->expects($this->any())
            ->method('getEventObject')
            ->willReturn('event_object');
        $this->appResourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $exception  = new Exception('Expected Exception');
        $this->modelMock->expects($this->any())
            ->method('getId')
            ->willThrowException($exception);
        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');
        $this->connectionMock->expects($this->once())
            ->method('rollback');
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'event_prefix_save_attribute_before',
                [
                    'event_object' =>  $this->attribute,
                    'object' => $this->modelMock,
                    'attribute' => ['attribute']
                ]
            );
        $this->attribute->saveAttribute($this->modelMock, 'attribute');
    }
}
