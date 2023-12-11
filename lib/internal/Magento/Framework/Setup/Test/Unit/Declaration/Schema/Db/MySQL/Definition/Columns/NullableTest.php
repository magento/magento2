<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean as BooleanColumnDto;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NullableTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Nullable
     */
    private $nullable;

    protected function setUp(): void
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
        /** @var BooleanColumnDto|MockObject $column */
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
        /** @var BooleanColumnDto|MockObject $column */
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
        /** @var ElementInterface|MockObject $column */
        $column = $this->getMockBuilder(ElementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertEquals(
            '',
            $this->nullable->toDefinition($column)
        );
    }
}
