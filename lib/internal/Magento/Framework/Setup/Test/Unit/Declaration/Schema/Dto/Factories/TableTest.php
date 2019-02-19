<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Dto\Factories;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

/**
 * Test table factory
 */
class TableTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Setup\Declaration\Schema\Dto\Factories\Table */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceConnectionMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
             ->disableOriginalConstructor()
             ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\Setup\Declaration\Schema\Dto\Factories\Table::class,
            [
                'objectManager' => $this->objectManagerMock,
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    public function testCreate()
    {
        $this->resourceConnectionMock->expects(self::once())
            ->method('getTablePrefix')
            ->willReturn('pf_');
        $data = [
            'name' => 'some_table',
            'engine' => null,
        ];
        $expectedData = [
            'name' => 'pf_some_table',
            'engine' => 'innodb',
            'nameWithoutPrefix' => 'some_table',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'onCreate' => ''
        ];
        $this->objectManagerMock->expects(self::once())
            ->method('create')
            ->with(Table::class, $expectedData);
        $this->model->create($data);
    }

    public function testCreateWithPrefix()
    {
        $this->resourceConnectionMock->expects(self::once())
            ->method('getTablePrefix')
            ->willReturn('pf_');
        $data = [
            'name' => 'pf_some_table',
            'engine' => 'memory',
            'nameWithoutPrefix' => 'some_table'
        ];
        $expectedData = [
            'name' => 'pf_some_table',
            'engine' => 'memory',
            'nameWithoutPrefix' => 'some_table',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'onCreate' => ''
        ];
        $this->objectManagerMock->expects(self::once())
            ->method('create')
            ->with(Table::class, $expectedData);
        $this->model->create($data);
    }
}
