<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultPriceTest extends TestCase
{
    /**
     * @var DefaultPrice
     */
    private $model;

    /**
     * @var MockObject
     */
    private $resourceMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManagerHelper->getObject(
            DefaultPrice::class,
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
