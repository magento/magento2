<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Website;

use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Website\ReadHandler;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModel;

class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ResourceModel\Website\Link | \PHPUnit_Framework_MockObject_MockObject */
    private $websiteLink;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $extensionAttributes;

    /** @var  ReadHandler  */
    private $readHandler;

    public function setUp()
    {
        $this->websiteLink = $this->getMockBuilder(ResourceModel\Website\Link::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributes = $this->getMockBuilder(ProductExtension::class)
            ->setMethods(['setWebsiteIds', 'getWebsiteIds'])
            ->disableArgumentCloning()
            ->getMock();
        $this->readHandler = new ReadHandler($this->websiteLink);
    }

    public function testExecuteWithNonCachedExtensionAttributes()
    {
        $productId = 1;
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($productId);
        $websiteIds = [1,2];
        $this->websiteLink->expects($this->once())
            ->method("getWebsiteIdsByProductId")
            ->with($productId)
            ->willReturn($websiteIds);
        $product->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->once())
            ->method("getWebsiteIds")
            ->willReturn(null);

        $product->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributes);

        $this->assertEquals($this->readHandler->execute($product, []), $product);
    }

    public function testExecuteWithCachedWebsiteIds()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $websiteIds = [1,2];
        $this->extensionAttributes->expects($this->once())
            ->method("getWebsiteIds")
            ->willReturn($websiteIds);
        $product->expects($this->never())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributes);
        $product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->assertEquals($this->readHandler->execute($product, []), $product);
    }
}
