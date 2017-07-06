<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\AdditionalInfo;

class AdditionalInfoTest extends \PHPUnit_Framework_TestCase
{
    /** @var  AdditionalInfo */
    private $model;

    public function setUp()
    {
        $this->model = new AdditionalInfo();
    }
    public function testGet()
    {
        $productRenderInfo = $this->getMock(ProductRenderInterface::class);
        $productRenderInfo->expects($this->once())
            ->method('setIsSalable')
            ->with(true);
        $productRenderInfo->expects($this->once())
            ->method('setName')
            ->with('simple');
        $productRenderInfo->expects($this->once())
            ->method('setId')
            ->with(1);
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('isSalable')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');
        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn('simple');
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->model->collect($productMock, $productRenderInfo);
    }
}
