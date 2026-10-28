<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Rss;

use Magento\Backend\Block\Context;
use Magento\Catalog\Block\Adminhtml\Rss\NotifyStock;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Rss\Product\NotifyStock as RssNotifyStock;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotifyStockTest extends TestCase
{
    /**
     * @var NotifyStock
     */
    protected $block;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var RssNotifyStock|MockObject
     */
    protected $rssModel;

    /**
     * @var UrlBuilderInterface|MockObject
     */
    protected $rssUrlBuilder;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $rssFeed = [
        'title' => 'Low Stock Products',
        'description' => 'Low Stock Products',
        'link' => 'http://magento.com/rss/feeds/index/type/notifystock',
        'charset' => 'UTF-8',
        'entries' => [
            [
                'title' => 'Low Stock Product',
                'description' => 'Low Stock Product has reached a quantity of 1.',
                'link' => 'http://magento.com/catalog/product/edit/id/1',

            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->rssModel = $this->createPartialMock(
            RssNotifyStock::class,
            ['getProductsCollection']
        );
        $this->rssUrlBuilder = $this->createMock(UrlBuilderInterface::class);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            NotifyStock::class,
            [
                'urlBuilder' => $this->urlBuilder,
                'rssModel' => $this->rssModel,
                'rssUrlBuilder' => $this->rssUrlBuilder
            ]
        );
    }

    public function testGetRssData()
    {
        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->willReturn('http://magento.com/rss/feeds/index/type/notifystock');
        $item = $this->createPartialMock(Product::class, ['__sleep', 'getId', 'getQty', 'getName']);
        $item->expects($this->once())->method('getId')->willReturn(1);
        $item->expects($this->once())->method('getQty')->willReturn(1);
        $item->method('getName')->willReturn('Low Stock Product');

        $this->rssModel->expects($this->once())->method('getProductsCollection')
            ->willReturn([$item]);
        $this->urlBuilder->expects($this->once())->method('getUrl')
            ->with('catalog/product/edit', ['id' => 1, '_secure' => true, '_nosecret' => true])
            ->willReturn('http://magento.com/catalog/product/edit/id/1');

        $data = $this->block->getRssData();
        $this->assertIsString($data['title']);
        $this->assertIsString($data['description']);
        $this->assertIsString($data['entries'][0]['description']);
        $this->assertEquals($this->rssFeed, $data);
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(600, $this->block->getCacheLifetime());
    }

    public function testIsAllowed()
    {
        $this->assertTrue($this->block->isAllowed());
    }

    public function testGetFeeds()
    {
        $this->assertEmpty($this->block->getFeeds());
    }
}
