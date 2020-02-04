<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Constraints;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Constraints\Internal;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal as InternalConstraintDto;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

/**
 * Test for internal (primary key, unique key) constraint definition.
 *
 * @package Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Constraints
 */
class InternalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Internal
     */
    private $internal;

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
        $this->internal = $this->objectManager->getObject(
            Internal::class,
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
        /** @var InternalConstraintDto|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->getMockBuilder(InternalConstraintDto::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tableMock = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        $constraint->expects($this->any())->method('getTable')->willReturn($tableMock);
        $tableMock->expects($this->any())->method('getResource')->willReturn('default');
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('default')
            ->willReturn($adapterMock);
        $constraint->expects($this->any())->method('getName')->willReturn($name);
        $constraint->expects($this->any())->method('getType')->willReturn($type);
        $constraint->expects($this->any())->method('getColumnNames')->willReturn($columns);
        $adapterMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnCallback(
                function ($name) {
                    return '`' . $name . '`';
                }
            );

        $this->assertEquals(
            $expectedExpression,
            $this->internal->toDefinition($constraint)
        );
    }

    /**
     * @return array
     */
    public function toDefinitionDataProvider()
    {
        return [
            [
                'name' => 'constraint_name_primary',
                'type' => 'primary',
                'columns' => ['id', 'parent_id'],
                'expectedExpression' => "CONSTRAINT  PRIMARY KEY (`id`,`parent_id`)"
            ],
            [
                'name' => 'constraint_name_unique',
                'type' => 'unique',
                'columns' => ['id', 'parent_id'],
                'expectedExpression' => "CONSTRAINT `constraint_name_unique` UNIQUE KEY (`id`,`parent_id`)"
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
        $result = $this->internal->fromDefinition($definition);
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
                    'Key_name' => 'PRIMARY',
                    'Column_name' => 'id',
                ],
                'excpectedDefiniton' => [
                    'name' => 'PRIMARY',
                    'column' => ['id' => 'id'],
                    'type' => 'primary',
                ],
            ],
            [
                'definition' => [
                    'Key_name' => 'unique_key_1',
                    'Column_name' => 'parent_id',
                ],
                'excpectedDefiniton' => [
                    'name' => 'unique_key_1',
                    'column' => ['parent_id' => 'parent_id'],
                    'type' => 'unique',
                ],
            ],
        ];
    }
}
