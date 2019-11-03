<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Unsigned;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer as IntegerColumnDto;

class UnsignedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Unsigned
     */
    private $unsigned;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->unsigned = $this->objectManager->getObject(
            Unsigned::class
        );
    }

    /**
     * Test conversion to definition of column with unsigned flag.
     */
    public function testToDefinition()
    {
        /** @var IntegerColumnDto|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(IntegerColumnDto::class)
            ->disableOriginalConstructor()
            ->setMethods(['isUnsigned'])
            ->getMock();
        $column->expects($this->any())
            ->method('isUnsigned')
            ->willReturn(true);
        $this->assertEquals(
            'UNSIGNED',
            $this->unsigned->toDefinition($column)
        );
    }

    /**
     * Test conversion to definition of column with no unsigned flag.
     */
    public function testToDefinitionNotUnsigned()
    {
        /** @var IntegerColumnDto|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(IntegerColumnDto::class)
            ->disableOriginalConstructor()
            ->setMethods(['isUnsigned'])
            ->getMock();
        $column->expects($this->any())
            ->method('isUnsigned')
            ->willReturn(false);
        $this->assertEquals(
            '',
            $this->unsigned->toDefinition($column)
        );
    }

    public function testFromDefinition()
    {
        $data = [
            'definition' => 'NOT NULL UNSIGNED'
        ];
        $expectedData = $data;
        $expectedData['unsigned'] = true;
        $this->assertEquals(
            $expectedData,
            $this->unsigned->fromDefinition($data)
        );
    }

    public function testFromDefinitionSigned()
    {
        $data = [
            'definition' => 'NOT NULL'
        ];
        $expectedData = $data;
        $expectedData['unsigned'] = false;
        $this->assertEquals(
            $expectedData,
            $this->unsigned->fromDefinition($data)
        );
    }
}
