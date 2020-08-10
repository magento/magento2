<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Website;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Website\SaveHandler;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    /** @var  ResourceModel\Website\Link|MockObject */
    private $productWebsiteLink;

    /** @var  StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var SaveHandler */
    private $saveHandler;

    /** @var  ProductInterface|MockObject */
    private $product;

    protected function setUp(): void
    {
        $this->productWebsiteLink = $this->getMockBuilder(Link::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->product = $this->getMockForAbstractClass(ProductInterface::class);
        $this->saveHandler = new SaveHandler($this->productWebsiteLink, $this->storeManager);
    }

    public function testWithMultipleStoreMode()
    {
        $websiteIds = [1,2];
        $this->storeManager->expects($this->once())
            ->method("isSingleStoreMode")
            ->willReturn(false);
        $extensionAttributes = $this->getMockBuilder(ExtensionAttributesInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteIds', 'setWebsiteIds'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn($websiteIds);
        $this->product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->productWebsiteLink->expects($this->once())
            ->method('saveWebsiteIds')
            ->with($this->product, $websiteIds);

        $this->assertEquals($this->product, $this->saveHandler->execute($this->product, []));
    }

    public function testWithEmptyWebsiteIds()
    {
        $extensionAttributes = $this->getMockBuilder(ExtensionAttributesInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteIds', 'setWebsiteIds'])
            ->getMockForAbstractClass();
        $this->product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->productWebsiteLink->expects($this->never())
            ->method('saveWebsiteIds')
            ->with($this->product, null);

        $this->assertEquals($this->product, $this->saveHandler->execute($this->product, []));
    }

    public function testWithSingleStoreMode()
    {
        $defaultWebsiteId = 1;
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($defaultWebsiteId);
        $this->storeManager->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($store);
        $this->storeManager->expects($this->once())
            ->method("isSingleStoreMode")
            ->willReturn(true);

        $this->productWebsiteLink->expects($this->once())
            ->method('saveWebsiteIds')
            ->with($this->product, [$defaultWebsiteId]);

        $this->assertEquals($this->product, $this->saveHandler->execute($this->product, []));
    }
}
