<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

class TableTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Setup\Model\Declaration\Schema\Dto\Factories\Table */
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
            \Magento\Setup\Model\Declaration\Schema\Dto\Factories\Table::class,
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
            'nameWithoutPrefix' => 'some_table'
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
            'nameWithoutPrefix' => 'some_table'
        ];
        $this->objectManagerMock->expects(self::once())
            ->method('create')
            ->with(Table::class, $expectedData);
        $this->model->create($data);
    }
}
