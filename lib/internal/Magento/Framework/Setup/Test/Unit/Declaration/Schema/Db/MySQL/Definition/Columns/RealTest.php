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
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Real;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Unsigned;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Real as RealColumnDto;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RealTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Real
     */
    private $real;

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
     * @var Unsigned|MockObject
     */
    private $unsignedMock;

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
        $this->unsignedMock = $this->getMockBuilder(Unsigned::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->real = $this->objectManager->getObject(
            Real::class,
            [
                'nullable' => $this->nullableMock,
                'unsigned' => $this->unsignedMock,
                'resourceConnection' => $this->resourceConnectionMock,
                'comment' => $this->commentMock,
            ]
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinitionNoScale()
    {
        /** @var RealColumnDto|MockObject $column */
        $column = $this->getMockBuilder(RealColumnDto::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column->expects($this->any())
            ->method('getName')
            ->willReturn('col');
        $column->expects($this->any())
            ->method('getType')
            ->willReturn('float');
        $column->expects($this->any())
            ->method('getPrecision')
            ->willReturn(0);
        $column->expects($this->any())
            ->method('getScale')
            ->willReturn(0);
        $column->expects($this->any())
            ->method('getDefault')
            ->willReturn(0);
        $this->unsignedMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('UNSIGNED');
        $this->nullableMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('NOT NULL');
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $adapterMock->expects($this->once())
            ->method('quoteIdentifier')
            ->with('col')
            ->willReturn('`col`');
        $this->commentMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('COMMENT "Comment"');
        $this->assertEquals(
            '`col` float UNSIGNED NOT NULL DEFAULT 0 COMMENT "Comment"',
            $this->real->toDefinition($column)
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinition()
    {
        /** @var RealColumnDto|MockObject $column */
        $column = $this->getMockBuilder(RealColumnDto::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column->expects($this->any())
            ->method('getName')
            ->willReturn('col');
        $column->expects($this->any())
            ->method('getType')
            ->willReturn('float');
        $column->expects($this->any())
            ->method('getPrecision')
            ->willReturn(10);
        $column->expects($this->any())
            ->method('getScale')
            ->willReturn(4);
        $column->expects($this->any())
            ->method('getDefault')
            ->willReturn(0);
        $this->unsignedMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('UNSIGNED');
        $this->nullableMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('NOT NULL');
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $adapterMock->expects($this->once())
            ->method('quoteIdentifier')
            ->with('col')
            ->willReturn('`col`');
        $this->commentMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('COMMENT "Comment"');
        $this->assertEquals(
            '`col` float(10, 4) UNSIGNED NOT NULL DEFAULT 0 COMMENT "Comment"',
            $this->real->toDefinition($column)
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinitionNoDefault()
    {
        /** @var RealColumnDto|MockObject $column */
        $column = $this->getMockBuilder(RealColumnDto::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column->expects($this->any())
            ->method('getName')
            ->willReturn('col');
        $column->expects($this->any())
            ->method('getType')
            ->willReturn('float');
        $column->expects($this->any())
            ->method('getPrecision')
            ->willReturn(10);
        $column->expects($this->any())
            ->method('getScale')
            ->willReturn(4);
        $column->expects($this->any())
            ->method('getDefault')
            ->willReturn(null);
        $this->unsignedMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('UNSIGNED');
        $this->nullableMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('NOT NULL');
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $adapterMock->expects($this->once())
            ->method('quoteIdentifier')
            ->with('col')
            ->willReturn('`col`');
        $this->commentMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('COMMENT "Comment"');
        $this->assertEquals(
            '`col` float(10, 4) UNSIGNED NOT NULL  COMMENT "Comment"',
            $this->real->toDefinition($column)
        );
    }

    /**
     * Test from definition conversion.
     *
     * @param array $definition
     * @param bool $expectedPrecision
     * @dataProvider definitionDataProvider()
     */
    public function testFromDefinition($definition, $expectedPrecision = false, $expectedScale = false)
    {
        $expectedData = [
            'definition' => $definition,
        ];
        if ($expectedPrecision) {
            $expectedData['precision'] = $expectedPrecision;
            $expectedData['scale'] = $expectedScale;
        }
        $this->unsignedMock->expects($this->any())->method('fromDefinition')->willReturnArgument(0);
        $this->nullableMock->expects($this->any())->method('fromDefinition')->willReturnArgument(0);
        $result = $this->real->fromDefinition(['definition' => $definition]);
        $this->assertEquals($expectedData, $result);
    }

    /**
     * @return array
     */
    public static function definitionDataProvider()
    {
        return [
            ['float'],
            ['float(10,4)', 10, 4],
            ['float(10)', false, false],
            ['decimal(10)', 10, 0],
            ['decimal(10, 6)', 10, 6],
            ['double(10, 6)', 10, 6],
            ['double', false, false],
            ['double(10)', false, false],
        ];
    }
}
