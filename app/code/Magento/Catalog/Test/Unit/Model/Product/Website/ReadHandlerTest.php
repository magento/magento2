<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Website;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Website\ReadHandler;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModel;
use PHPUnit\Framework\MockObject\MockObject;

class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ResourceModel\Website\Link|MockObject */
    private $websiteLinkMock;

    /** @var MockObject  */
    private $extensionAttributesMock;

    /** @var  ReadHandler  */
    private $readHandler;

    public function setUp()
    {
        $this->websiteLinkMock = $this->getMockBuilder(ResourceModel\Website\Link::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['setWebsiteIds', 'getWebsiteIds'])
            ->disableArgumentCloning()
            ->getMockForAbstractClass();
        $this->readHandler = new ReadHandler($this->websiteLinkMock);
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
        $this->websiteLinkMock->expects($this->once())
            ->method("getWebsiteIdsByProductId")
            ->with($productId)
            ->willReturn($websiteIds);
        $product->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->extensionAttributesMock->expects($this->once())
            ->method("getWebsiteIds")
            ->willReturn(null);

        $product->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributesMock);

        $this->assertEquals($this->readHandler->execute($product, []), $product);
    }

    public function testExecuteWithCachedWebsiteIds()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $websiteIds = [1,2];
        $this->extensionAttributesMock->expects($this->once())
            ->method("getWebsiteIds")
            ->willReturn($websiteIds);
        $product->expects($this->never())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributesMock);
        $product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->assertEquals($this->readHandler->execute($product, []), $product);
    }
}
