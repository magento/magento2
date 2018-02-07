<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Plugin\Model\Product;

use \Magento\CatalogRule\Plugin\Model\Product\Action;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogRule\Plugin\Model\Product\Action */
    protected $action;

    /** @var \Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $productRuleProcessor;

    protected function setUp()
    {
        $this->productRuleProcessor = $this->getMockBuilder(
            'Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor'
        )->disableOriginalConstructor()
        ->setMethods(['reindexList'])
        ->getMock();

        $this->action = new Action($this->productRuleProcessor);
    }

    public function testAfterUpdateAttributes()
    {
        $subject = $this->getMockBuilder('Magento\Catalog\Model\Product\Action')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $result = $this->getMockBuilder('Magento\Catalog\Model\Product\Action')
            ->disableOriginalConstructor()
            ->setMethods(['getAttributesData', 'getProductIds'])
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
        $subject = $this->getMockBuilder('Magento\Catalog\Model\Product\Action')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $result = $this->getMockBuilder('Magento\Catalog\Model\Product\Action')
            ->disableOriginalConstructor()
            ->setMethods(['getAttributesData', 'getProductIds'])
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
