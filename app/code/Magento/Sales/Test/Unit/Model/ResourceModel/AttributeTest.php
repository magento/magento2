<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel;

/**
 * Class AttributeTest
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Attribute|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $attribute;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Sales\Model\AbstractModel|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $modelMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    protected function setUp(): void
    {
        $this->appResourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->modelMock = $this->getMockForAbstractClass(
            \Magento\Sales\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['__wakeup', 'getId', 'getEventPrefix', 'getEventObject']
        );
        $this->connectionMock = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['describeTable', 'insert', 'lastInsertId', 'beginTransaction', 'rollback', 'commit']
        );
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->willReturn([]);
        $this->connectionMock->expects($this->any())
            ->method('insert');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');
        $this->attribute = new \Magento\Sales\Model\ResourceModel\Attribute(
            $this->appResourceMock,
            $this->eventManagerMock
        );
    }

    /**
     * @throws \Exception
     */
    public function testSave()
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
        $this->eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('event_prefix_save_attribute_before', [
                'event_object' => $this->attribute,
                'object' => $this->modelMock,
                'attribute' => ['attribute']
            ]);
        $this->eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('event_prefix_save_attribute_after', [
                'event_object' => $this->attribute,
                'object' => $this->modelMock,
                'attribute' => ['attribute']
            ]);
        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');
        $this->connectionMock->expects($this->once())
            ->method('commit');
        $this->assertEquals($this->attribute, $this->attribute->saveAttribute($this->modelMock, 'attribute'));
    }

    /**
     * @throws \Exception
     */
    public function testSaveFailed()
    {
        $this->expectException(\Exception::class);
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
        $exception  = new \Exception('Expected Exception');
        $this->modelMock->expects($this->any())
            ->method('getId')
            ->will($this->throwException($exception));
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
