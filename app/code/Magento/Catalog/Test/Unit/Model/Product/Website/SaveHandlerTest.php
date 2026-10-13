<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Website;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Website\SaveHandler;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    use MockCreationTrait;

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
        $this->productWebsiteLink = $this->createMock(Link::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->product = $this->createMock(ProductInterface::class);
        $this->saveHandler = new SaveHandler($this->productWebsiteLink, $this->storeManager);
    }

    public function testWithMultipleStoreMode()
    {
        $websiteIds = [1,2];
        $this->storeManager->expects($this->once())
            ->method("isSingleStoreMode")
            ->willReturn(false);
        $extensionAttributes = $this->createPartialMockWithReflection(
            ExtensionAttributesInterface::class,
            ['getWebsiteIds', 'setWebsiteIds']
        );
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
        /** @var ExtensionAttributesInterface $extensionAttributes */
        $extensionAttributes = $this->createPartialMockWithReflection(
            ExtensionAttributesInterface::class,
            ['getWebsiteIds']
        );
        $extensionAttributes->method('getWebsiteIds')->willReturn(null);
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
        $store = $this->createMock(StoreInterface::class);
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
