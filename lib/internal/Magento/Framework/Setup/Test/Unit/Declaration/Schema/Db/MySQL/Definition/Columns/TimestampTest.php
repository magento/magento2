<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Timestamp;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Comment;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\OnUpdate;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean as BooleanColumn;

class TimestampTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Timestamp
     */
    private $timestamp;

    /**
     * @var Nullable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nullableMock;

    /**
     * @var Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var OnUpdate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $onUpdateMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->nullableMock = $this->getMockBuilder(Nullable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentMock = $this->getMockBuilder(Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->onUpdateMock = $this->getMockBuilder(OnUpdate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timestamp = $this->objectManager->getObject(
            Timestamp::class,
            [
                'onUpdate' => $this->onUpdateMock,
                'nullable' => $this->nullableMock,
                'comment' => $this->commentMock,
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    /**
     * Test conversion to definition.
     * @dataProvider toDefinitionProvider()
     * @param string $default
     * @param bool $nullable
     * @param bool $onUpdate
     * @param string $expectedStatement
     */
    public function testToDefinition($default, $nullable, $onUpdate, $expectedStatement)
    {
        /** @var BooleanColumn|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(BooleanColumn::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column->expects($this->any())
            ->method('getName')
            ->willReturn('col');
        $column->expects($this->any())
            ->method('getType')
            ->willReturn('DATETIME');
        $column->expects($this->any())
            ->method('getDefault')
            ->willReturn($default);
        $adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $adapterMock->expects($this->once())
            ->method('quoteIdentifier')
            ->with('col')
            ->willReturn('`col`');
        if ($nullable) {
            $this->nullableMock->expects($this->any())
                ->method('toDefinition')
                ->with($column)
                ->willReturn('NULL');
        } else {
            $this->nullableMock->expects($this->any())
                ->method('toDefinition')
                ->with($column)
                ->willReturn('NOT NULL');
        }
        $this->commentMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('COMMENT "Comment"');
        if ($onUpdate) {
            $this->onUpdateMock->expects($this->any())
                ->method('toDefinition')
                ->with($column)
                ->willReturn('ON UPDATE CURRENT_TIMESTAMP');
        }
        $this->assertEquals(
            $expectedStatement,
            $this->timestamp->toDefinition($column)
        );
    }

    /**
     * @return array
     */
    public function toDefinitionProvider()
    {
        return [
            [
                'default' => 'NULL', // xsd replaced for no default value set in xml
                'nullable' => true,
                'onUpdate' => 'CURRENT_TIMESTAMP',
                'expectedStatement' => '`col` DATETIME NULL  ON UPDATE CURRENT_TIMESTAMP COMMENT "Comment"',
            ],
            [
                'default' => 'NULL', // xsd replaced for no default value set in xml
                'nullable' => true,
                'onUpdate' => 'CURRENT_TIMESTAMP',
                'expectedStatement' => '`col` DATETIME NULL  ON UPDATE CURRENT_TIMESTAMP COMMENT "Comment"',
            ],
            [
                'default' => 'NULL', // xsd replaced for no default value set in xml
                'nullable' => false,
                'onUpdate' => 'CURRENT_TIMESTAMP',
                'expectedStatement' => '`col` DATETIME NOT NULL  ON UPDATE CURRENT_TIMESTAMP COMMENT "Comment"',
            ],
            [
                'default' => 'CURRENT_TIMESTAMP',
                'nullable' => false,
                'onUpdate' => false,
                'expectedStatement' => '`col` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT "Comment"',
            ],
            [
                'default' => 'CURRENT_TIMESTAMP',
                'nullable' => true,
                'onUpdate' => 'CURRENT_TIMESTAMP',
                'expectedStatement' => '`col` DATETIME NULL DEFAULT CURRENT_TIMESTAMP '
                    . 'ON UPDATE CURRENT_TIMESTAMP COMMENT "Comment"',
            ]
        ];
    }
}
