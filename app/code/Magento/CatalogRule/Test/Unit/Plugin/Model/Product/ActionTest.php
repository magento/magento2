<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Plugin\Model\Product;

use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Plugin\Model\Product\Action;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{
    /** @var Action */
    protected $action;

    /** @var ProductRuleProcessor|MockObject */
    protected $productRuleProcessor;

    protected function setUp(): void
    {
        $this->productRuleProcessor = $this->getMockBuilder(
            ProductRuleProcessor::class
        )->disableOriginalConstructor()
            ->onlyMethods(['reindexList'])
            ->getMock();

        $this->action = new Action($this->productRuleProcessor);
    }

    public function testAfterUpdateAttributes()
    {
        $subject = $this->getMockBuilder(\Magento\Catalog\Model\Product\Action::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $result = $this->getMockBuilder(\Magento\Catalog\Model\Product\Action::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttributesData', 'getProductIds'])
            ->getMock();

        $result->expects($this->once())
            ->method('getAttributesData')
            ->willReturn([]);

        $result->expects($this->never())
            ->method('getProductIds');

        $this->productRuleProcessor->expects($this->never())
            ->method('reindexList');

        $this->action->afterUpdateAttributes($subject, $result);
    }

    public function testAfterUpdateAttributesWithPrice()
    {
        $productIds = [1, 2, 3];
        $subject = $this->getMockBuilder(\Magento\Catalog\Model\Product\Action::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $result = $this->getMockBuilder(\Magento\Catalog\Model\Product\Action::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttributesData', 'getProductIds'])
            ->getMock();

        $result->expects($this->once())
            ->method('getAttributesData')
            ->willReturn(['price' => 100]);

        $result->expects($this->once())
            ->method('getProductIds')
            ->willReturn($productIds);

        $this->productRuleProcessor->expects($this->once())
            ->method('reindexList')
            ->with($productIds);

        $this->action->afterUpdateAttributes($subject, $result);
    }
}
