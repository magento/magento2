<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Boolean;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Comment;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Identity;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Integer;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Real;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Unsigned;

class RealTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Real
     */
    private $real;

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
     * @var Unsigned|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unsignedMock;

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
        /** @var \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real::class)
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
        $adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        /** @var \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real::class)
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
            ->willReturn(4);
        $column->expects($this->any())
            ->method('getScale')
            ->willReturn(10);
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
        $adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        /** @var \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real::class)
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
            ->willReturn(4);
        $column->expects($this->any())
            ->method('getScale')
            ->willReturn(10);
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
        $adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
     * @param bool $expectedScale
     * @dataProvider definitionDataProvider()
     */
    public function testFromDefinition($definition, $expectedScale = false, $expectedPrecision = false)
    {
        $expectedData = [
            'definition' => $definition,
        ];
        if ($expectedScale) {
            $expectedData['scale'] = $expectedScale;
            $expectedData['precision'] = $expectedPrecision;
        }
        $this->unsignedMock->expects($this->any())->method('fromDefinition')->willReturnArgument(0);
        $this->nullableMock->expects($this->any())->method('fromDefinition')->willReturnArgument(0);
        $result = $this->real->fromDefinition(['definition' => $definition]);
        $this->assertEquals($expectedData, $result);
    }

    /**
     * @return array
     */
    public function definitionDataProvider()
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
