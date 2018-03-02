<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Rss;

use Magento\Catalog\Block\Adminhtml\Rss\NotifyStock\DescriptionProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NotifyStockTest
 * @package Magento\Catalog\Block\Adminhtml\Rss
 */
class NotifyStockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Rss\NotifyStock
     */
    protected $block;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\NotifyStock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
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

    /**
     * @var DescriptionProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $descriptionProvider;

    protected function setUp()
    {
        $this->rssModel = $this->getMockBuilder(\Magento\Catalog\Model\Rss\Product\NotifyStock::class)
            ->setMethods(['getItemsCollection', '__wakeup'])
            ->disableOriginalConstructor()->getMock();
        $this->rssUrlBuilder = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->descriptionProvider = $this->createMock(DescriptionProvider::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Block\Adminhtml\Rss\NotifyStock::class,
            [
                'urlBuilder' => $this->urlBuilder,
                'rssModel' => $this->rssModel,
                'rssUrlBuilder' => $this->rssUrlBuilder,
                'descriptionProvider' => $this->descriptionProvider,
            ]
        );
    }

    public function testGetRssData()
    {
        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->will($this->returnValue('http://magento.com/rss/feeds/index/type/notifystock'));
        $item = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__sleep', '__wakeup', 'getId', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->once())->method('getId')->will($this->returnValue(1));
        $item->expects($this->any())->method('getData')->will($this->returnValue('Low Stock Product'));

        $this->descriptionProvider->expects($this->once())->method('execute')->willReturn(
            __('Low Stock Product has reached a quantity of 1.')
        );
        $this->rssModel->expects($this->once())->method('getItemsCollection')
            ->will($this->returnValue([$item]));
        $this->urlBuilder->expects($this->once())->method('getUrl')
            ->with('catalog/product/edit', ['id' => 1, '_secure' => true, '_nosecret' => true])
            ->will($this->returnValue('http://magento.com/catalog/product/edit/id/1'));
        $this->assertEquals($this->rssFeed, $this->block->getRssData());
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
