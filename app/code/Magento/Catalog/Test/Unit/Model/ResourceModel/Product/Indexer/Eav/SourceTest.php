<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerStateFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerStateFactory = $this->getMockBuilder(\Magento\Indexer\Model\Indexer\StateFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->model = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source::class,
            [
                'stateFactory' => $this->indexerStateFactory,
                'resources' => $this->resourceMock
            ]
        );
    }

    public function testGetMainTable()
    {
        $indexerStateModel = $this->getMockBuilder(\Magento\Indexer\Model\Indexer\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->once())->method('getTableName')->willReturn('catalog_product_index_eav');
        $this->indexerStateFactory->expects($this->once())->method('create')->willReturn($indexerStateModel);
        $indexerStateModel->expects($this->once())
            ->method('loadByIndexer')
            ->with(\Magento\Catalog\Model\Indexer\Product\Eav\Processor::INDEXER_ID)
            ->willReturnSelf();
        $indexerStateModel->expects($this->once())->method('getTableSuffix')->willReturn('');
        $this->assertEquals('catalog_product_index_eav_replica', $this->model->getMainTable());
    }
}
