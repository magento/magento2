<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Blob;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Comment;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BlobTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Blob
     */
    private $blob;

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
        $this->blob = $this->objectManager->getObject(
            Blob::class,
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
        /** @var ElementInterface|MockObject $column */
        $column = $this->getMockBuilder(ElementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $column->expects($this->any())
            ->method('getName')
            ->willReturn('col');
        $column->expects($this->any())
            ->method('getType')
            ->willReturn('blob');
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
            '`col` blob NULL COMMENT "Comment"',
            $this->blob->toDefinition($column)
        );
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
        $result = $this->blob->fromDefinition(['definition' => $definition]);
        $this->assertEquals($expectedData, $result);
    }

    /**
     * @return array
     */
    public function definitionDataProvider()
    {
        return [
            ['blob'],
            ['tinyblob'],
            ['mediumblob'],
            ['longblob'],
            ['text (555)', 555],
            ['tinytext'],
            ['mediumtext'],
            ['longtext'],
        ];
    }
}
