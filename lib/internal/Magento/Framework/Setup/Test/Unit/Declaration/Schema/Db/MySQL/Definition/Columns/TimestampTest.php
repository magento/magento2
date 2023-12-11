<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Comment;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\OnUpdate;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Timestamp;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean as BooleanColumn;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TimestampTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Timestamp
     */
    private $timestamp;

    /**
     * @var Nullable|MockObject
     */
    private $nullableMock;

    /**
     * @var Comment|MockObject
     */
    private $commentMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var OnUpdate|MockObject
     */
    private $onUpdateMock;

    protected function setUp(): void
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
        /** @var BooleanColumn|MockObject $column */
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
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
