<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductBatchSizeAdjuster;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductRelationsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeProductBatchSizeAdjusterTest extends TestCase
{
    /**
     * @var CompositeProductBatchSizeAdjuster
     */
    private $model;

    /**
     * @var MockObject|CompositeProductRelationsCalculator
     */
    private $relationsCalculatorMock;

    protected function setUp(): void
    {
        $this->relationsCalculatorMock = $this->getMockBuilder(CompositeProductRelationsCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new CompositeProductBatchSizeAdjuster($this->relationsCalculatorMock);
    }

    public function testAdjust()
    {
        $this->relationsCalculatorMock->expects($this->once())
            ->method('getMaxRelationsCount')
            ->willReturn(200);
        $this->assertEquals(25, $this->model->adjust(5000));
    }
}
