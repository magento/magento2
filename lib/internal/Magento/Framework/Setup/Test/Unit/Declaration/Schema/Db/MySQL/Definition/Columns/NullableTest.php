<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean as BooleanColumnDto;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

class NullableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Nullable
     */
    private $nullable;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->nullable = $this->objectManager->getObject(
            Nullable::class
        );
    }

    /**
     * Test conversion to definition of nullable column.
     */
    public function testToDefinition()
    {
        /** @var BooleanColumnDto|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(BooleanColumnDto::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNullable'])
            ->getMock();
        $column->expects($this->any())
            ->method('isNullable')
            ->willReturn(true);
        $this->assertEquals(
            'NULL',
            $this->nullable->toDefinition($column)
        );
    }

    /**
     * Test conversion to definition for not nullable column.
     */
    public function testToDefinitionNotNull()
    {
        /** @var BooleanColumnDto|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(BooleanColumnDto::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNullable'])
            ->getMock();
        $column->expects($this->any())
            ->method('isNullable')
            ->willReturn(false);
        $this->assertEquals(
            'NOT NULL',
            $this->nullable->toDefinition($column)
        );
    }

    /**
     * Test conversion to definition of not nullable aware class.
     */
    public function testToDefinitionNotNullableAware()
    {
        /** @var ElementInterface|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(ElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(
            '',
            $this->nullable->toDefinition($column)
        );
    }
}
