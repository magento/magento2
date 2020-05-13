<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Table;

use Magento\Catalog\Model\Indexer\Product\Flat\Table\Builder;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    public function testAddColumn()
    {
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->once())->method('addColumn')
            ->with('test', Table::TYPE_INTEGER)
            ->willReturnSelf();
        $tableName = 'test_table';
        $this->connectionMock->expects($this->once())
            ->method('newTable')
            ->with($tableName)
            ->willReturn($table);
        $objectManagerHelper = new ObjectManager($this);
        /**
         * @var Builder $builder
         */
        $builder = $objectManagerHelper->getObject(
            Builder::class,
            [
                'connection' => $this->connectionMock,
                'tableName' => $tableName
            ]
        );
        $this->assertEquals($builder, $builder->addColumn('test', Table::TYPE_INTEGER));
    }
}
