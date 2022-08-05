<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test table factory
 */
class TableTest extends TestCase
{
    /** @var \Magento\Framework\Setup\Declaration\Schema\Dto\Factories\Table */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ObjectManagerInterface|MockObject */
    protected $objectManagerMock;

    /** @var ResourceConnection|MockObject */
    protected $resourceConnectionMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
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
