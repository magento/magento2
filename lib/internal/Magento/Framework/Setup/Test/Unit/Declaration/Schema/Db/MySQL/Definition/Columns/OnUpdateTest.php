<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\OnUpdate;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean as BooleanColumnDto;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Timestamp;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OnUpdateTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var OnUpdate
     */
    private $onUpdate;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->onUpdate = $this->objectManager->getObject(
            OnUpdate::class
        );
    }

    /**
     * Test conversion to definition of column with onUpdate statement.
     */
    public function testToDefinition()
    {
        /** @var Timestamp|MockObject $column */
        $column = $this->getMockBuilder(Timestamp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOnUpdate'])
            ->getMock();
        $column->expects($this->any())
            ->method('getOnUpdate')
            ->willReturn('on update');
        $this->assertEquals(
            'ON UPDATE CURRENT_TIMESTAMP',
            $this->onUpdate->toDefinition($column)
        );
    }

    /**
     * Test conversion to definition of column with no onUpdate statement.
     */
    public function testToDefinitionNonUpdate()
    {
        /** @var Timestamp|MockObject $column */
        $column = $this->getMockBuilder(Timestamp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOnUpdate'])
            ->getMock();
        $column->expects($this->any())
            ->method('getOnUpdate')
            ->willReturn(null);
        $this->assertEquals(
            '',
            $this->onUpdate->toDefinition($column)
        );
    }

    /**
     * Test conversion to definition of non-timestamp column.
     */
    public function testToDefinitionNonTimestamp()
    {
        /** @var BooleanColumnDto|MockObject $column */
        $column = $this->getMockBuilder(BooleanColumnDto::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(
            '',
            $this->onUpdate->toDefinition($column)
        );
    }
}
