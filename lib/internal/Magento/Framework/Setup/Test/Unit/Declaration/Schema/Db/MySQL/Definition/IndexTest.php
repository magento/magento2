<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index as IndexDto;

/**
 * Test for index (key) definition.
 *
 * @package Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Constraints
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Index
     */
    private $index;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->index = $this->objectManager->getObject(
            Index::class,
            [
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    /**
     * Test conversion to definition.
     *
     * @dataProvider toDefinitionDataProvider()
     */
    public function testToDefinition($name, $type, $columns, $expectedExpression)
    {
        /** @var IndexDto|\PHPUnit_Framework_MockObject_MockObject $index */
        $index = $this->getMockBuilder(IndexDto::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($adapterMock);

        $index->expects($this->any())->method('getName')->willReturn($name);
        $index->expects($this->any())->method('getIndexType')->willReturn($type);
        $index->expects($this->any())->method('getColumnNames')->willReturn($columns);
        $adapterMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnCallback(
                function ($name) {
                    return '`' . $name . '`';
                }
            );

        $this->assertEquals(
            $expectedExpression,
            $this->index->toDefinition($index)
        );
    }

    /**
     * @return array
     */
    public function toDefinitionDataProvider()
    {
        return [
            [
                'name' => 'ft_index',
                'type' => IndexDto::FULLTEXT_INDEX,
                'columns' => ['title', 'content'],
                'expectedExpression' => "FULLTEXT INDEX `ft_index` (`title`,`content`)"
            ],
            [
                'name' => 'ft_index',
                'type' => IndexDto::FULLTEXT_INDEX,
                'columns' => ['title'],
                'expectedExpression' => "FULLTEXT INDEX `ft_index` (`title`)"
            ],
            [
                'name' => 'ft_index',
                'type' => 'btree',
                'columns' => ['title'],
                'expectedExpression' => "INDEX `ft_index` (`title`)"
            ],
            [
                'name' => 'ft_index',
                'type' => 'HASH',
                'columns' => ['title'],
                'expectedExpression' => "INDEX `ft_index` (`title`)"
            ],
        ];
    }

    /**
     * Test from definition conversion.
     *
     * @param array $definition
     * @param array $expectedDefinition
     * @dataProvider definitionDataProvider()
     */
    public function testFromDefinition($definition, $expectedDefinition)
    {
        $result = $this->index->fromDefinition($definition);
        $this->assertEquals($expectedDefinition, $result);
    }

    /**
     * @return array
     */
    public function definitionDataProvider()
    {
        return [
            [
                'definition' => [
                    'Index_type' => 'FULLTEXT',
                    'Key_name' => 'ft_index',
                    'Column_name' => 'text',
                ],
                'excpectedDefiniton' => [
                    'indexType' => 'fulltext',
                    'name' => 'ft_index',
                    'column' => ['text' => 'text'],
                    'type' => 'index',
                ],
            ],
            [
                'definition' => [
                    'Index_type' => 'BTREE',
                    'Key_name' => 'bt_index',
                    'Column_name' => 'text',
                ],
                'excpectedDefiniton' => [
                    'indexType' => 'btree',
                    'name' => 'bt_index',
                    'column' => ['text' => 'text'],
                    'type' => 'index',
                ],
            ],
            [
                'definition' => [
                    'Index_type' => 'HASH',
                    'Key_name' => 'ht_index',
                    'Column_name' => 'text',
                ],
                'excpectedDefiniton' => [
                    'indexType' => 'hash',
                    'name' => 'ht_index',
                    'column' => ['text' => 'text'],
                    'type' => 'index',
                ],
            ]
        ];
    }
}
