<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Table;

/**
 * Class BuilderTest
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    public function testAddColumn()
    {
        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $table = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->once())->method('addColumn')
            ->with('test', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER)
            ->willReturnSelf();
        $tableName = 'test_table';
        $this->connectionMock->expects($this->once())
            ->method('newTable')
            ->with($tableName)
            ->willReturn($table);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /**
         * @var $builder \Magento\Catalog\Model\Indexer\Product\Flat\Table\Builder
         */
        $builder = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat\Table\Builder::class,
            [
                'connection' => $this->connectionMock,
                'tableName' => $tableName
            ]
        );
        $this->assertEquals($builder, $builder->addColumn('test', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER));
    }
}
