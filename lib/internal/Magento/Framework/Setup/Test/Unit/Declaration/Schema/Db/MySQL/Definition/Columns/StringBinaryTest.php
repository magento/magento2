<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Comment;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\StringBinary;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\StringBinary as StringBinaryColumn;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for StringBinary class.
 *
 */
class StringBinaryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StringBinary
     */
    private $stringBinary;

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
        $this->stringBinary = $this->objectManager->getObject(
            StringBinary::class,
            [
                'nullable' => $this->nullableMock,
                'comment' => $this->commentMock,
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinition()
    {
        /** @var StringBinaryColumn|MockObject $column */
        $column = $this->getMockBuilder(StringBinaryColumn::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column->expects($this->any())
            ->method('getName')
            ->willReturn('col');
        $column->expects($this->any())
            ->method('getType')
            ->willReturn('varchar');
        $column->expects($this->any())
            ->method('getLength')
            ->willReturn(50);
        $column->expects($this->any())
            ->method('getCollation')
            ->willReturn('utf8mb4_general_ci');
        $column->expects($this->any())
            ->method('getCharset')
            ->willReturn('utf8mb4');
        $column->expects($this->any())
            ->method('getDefault')
            ->willReturn('test');
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $adapterMock->expects($this->once())
            ->method('quoteIdentifier')
            ->with('col')
            ->willReturn('`col`');
        $this->nullableMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('NULL');
        $this->commentMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('COMMENT "Comment"');
        $this->assertEquals(
            '`col` varchar(50) NULL DEFAULT "test" COMMENT "Comment"',
            $this->stringBinary->toDefinition($column)
        );
    }

    /**
     * @param array $definition
     * @param bool $expectedLength
     * @dataProvider definitionDataProvider()
     */
    public function testGetBinaryDefaultValueFromDefinition($definition)
    {
        $defaultValue = 'test';
        if (preg_match('/^(binary|varbinary)/', $definition)) {
            $default = '0x' . bin2hex($defaultValue);
        } else {
            $default = $defaultValue;
        }

        $result = $this->stringBinary->fromDefinition(['definition' => $definition, 'default' => $default]);
        $this->assertEquals($result['default'], $defaultValue);
    }

    /**
     * Test from definition conversion.
     *
     * @param array $definition
     * @param bool $expectedLength
     * @dataProvider definitionDataProvider()
     */
    public function testFromDefinition($definition, $expectedLength = false)
    {
        $expectedData = [
            'definition' => $definition,
        ];
        if ($expectedLength) {
            $expectedData['length'] = $expectedLength;
        }
        $result = $this->stringBinary->fromDefinition(['definition' => $definition]);
        $this->assertEquals($expectedData, $result);
    }

    /**
     * @return array
     */
    public static function definitionDataProvider()
    {
        return [
            ['char'],
            ['char(30)', 30],
            ['varchar'],
            ['varchar (30)', 30],
            ['binary'],
            ['binary(20)', 20],
            ['varbinary'],
            ['varbinary(44)', 44],
        ];
    }
}
