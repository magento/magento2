<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\OnUpdate;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean as BooleanColumnDto;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Timestamp;

class OnUpdateTest extends \PHPUnit\Framework\TestCase
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
        /** @var Timestamp|\PHPUnit\Framework\MockObject\MockObject $column */
        $column = $this->getMockBuilder(Timestamp::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOnUpdate'])
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
        /** @var Timestamp|\PHPUnit\Framework\MockObject\MockObject $column */
        $column = $this->getMockBuilder(Timestamp::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOnUpdate'])
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
        /** @var BooleanColumnDto|\PHPUnit\Framework\MockObject\MockObject $column */
        $column = $this->getMockBuilder(BooleanColumnDto::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(
            '',
            $this->onUpdate->toDefinition($column)
        );
    }
}
