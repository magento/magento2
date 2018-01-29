<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\OnUpdate;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Boolean;

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

    protected function setUp()
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
        /** @var \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp::class)
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
        /** @var \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp::class)
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
        /** @var \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Boolean|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Dto\Columns\Boolean::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(
            '',
            $this->onUpdate->toDefinition($column)
        );
    }
}
