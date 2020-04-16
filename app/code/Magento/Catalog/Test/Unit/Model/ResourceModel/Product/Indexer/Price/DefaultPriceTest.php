<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DefaultPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice::class,
            [
                'resources' => $this->resourceMock
            ]
        );
    }

    public function testGetMainTable()
    {
        $this->resourceMock->expects($this->once())->method('getTableName')->willReturn('catalog_product_index_price');
        $this->assertEquals('catalog_product_index_price', $this->model->getMainTable());
    }
}
