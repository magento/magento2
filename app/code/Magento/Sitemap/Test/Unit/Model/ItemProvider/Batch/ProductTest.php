<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ItemProvider\Batch;

use Magento\Sitemap\Model\ItemProvider\Batch\Product;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\ProductFactory as BatchProductFactory;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /** @var ConfigReaderInterface|MockObject */
    private $configReader;

    /** @var BatchProductFactory|MockObject */
    private $batchProductFactory;

    /** @var SitemapItemInterfaceFactory|MockObject */
    private $itemFactory;

    /** @var Product */
    private $product;

    protected function setUp(): void
    {
        $this->configReader = $this->createMock(ConfigReaderInterface::class);
        $this->batchProductFactory = $this->createMock(BatchProductFactory::class);
        $this->itemFactory = $this->createMock(SitemapItemInterfaceFactory::class);

        $this->product = new Product(
            $this->configReader,
            $this->batchProductFactory,
            $this->itemFactory
        );
    }

    public function testGetItemsReturnsEmptyArrayWhenCollectionIsFalse()
    {
        $storeId = 1;
        $batchProduct = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getCollectionArray'])
            ->getMock();
        $batchProduct->expects($this->once())
            ->method('getCollectionArray')
            ->with($storeId)
            ->willReturn(false);

        $this->batchProductFactory->expects($this->once())
            ->method('create')
            ->willReturn($batchProduct);

        $result = $this->product->getItems($storeId);
        $this->assertSame([], $result);
    }

    public function testGetItemsReturnsMappedItems()
    {
        $storeId = 2;
        $priority = '0.5';
        $changeFrequency = 'daily';

        $itemData = [
            'url' => 'http://example.com/product.html',
            'updatedAt' => '2024-06-01',
            'images' => ['img1.jpg', 'img2.jpg']
        ];

        $productObj = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getUrl', 'getUpdatedAt', 'getImages'])
            ->getMock();
        $productObj->expects($this->once())->method('getUrl')->willReturn($itemData['url']);
        $productObj->expects($this->once())->method('getUpdatedAt')->willReturn($itemData['updatedAt']);
        $productObj->expects($this->once())->method('getImages')->willReturn($itemData['images']);

        $batchProduct = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getCollectionArray'])
            ->getMock();
        $batchProduct->expects($this->once())
            ->method('getCollectionArray')
            ->with($storeId)
            ->willReturn([$productObj]);

        $this->batchProductFactory->expects($this->once())
            ->method('create')
            ->willReturn($batchProduct);

        $this->configReader->expects($this->once())
            ->method('getPriority')
            ->with($storeId)
            ->willReturn($priority);

        $this->configReader->expects($this->once())
            ->method('getChangeFrequency')
            ->with($storeId)
            ->willReturn($changeFrequency);

        $expectedItem = new \stdClass();
        $this->itemFactory->expects($this->once())
            ->method('create')
            ->with([
                'url' => $itemData['url'],
                'updatedAt' => $itemData['updatedAt'],
                'images' => $itemData['images'],
                'priority' => $priority,
                'changeFrequency' => $changeFrequency,
            ])
            ->willReturn($expectedItem);

        $result = $this->product->getItems($storeId);
        $this->assertSame([$expectedItem], $result);
    }
}
