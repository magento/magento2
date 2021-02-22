<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Unit\Block\Adminhtml;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test RSS adminhtml block
 */
class RssTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Review\Block\Adminhtml\Rss
     */
    protected $block;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Review\Model\Rss|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rss;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerInterface = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->rss = $this->createPartialMock(\Magento\Review\Model\Rss::class, ['__wakeUp', 'getProductCollection']);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\Review\Block\Adminhtml\Rss::class,
            [
                'storeManager' => $this->storeManagerInterface,
                'rssModel' => $this->rss,
                'urlBuilder' => $this->urlBuilder,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetRssData()
    {
        $rssUrl = '';
        $rssData = [
            'title' => 'Pending product review(s)',
            'description' => 'Pending product review(s)',
            'link' => $rssUrl,
            'charset' => 'UTF-8',
            'entries' => [
                    'title' => 'Product: "Product Name" reviewed by: Product Nick',
                    'link' => 'http://product.magento.com',
                    'description' => [
                            'rss_url' => $rssUrl,
                            'name' => 'Product Name',
                            'summary' => 'Product Title',
                            'review' => 'Product Detail',
                            'store' => 'Store Name',

                        ],
                ],
        ];
        $productModel = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product::class,
            [
                'getStoreId',
                'getId',
                'getReviewId',
                'getName',
                'getDetail',
                'getTitle',
                'getNickname',
                'getProductUrl'
            ]
        );
        $storeModel = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerInterface->expects($this->once())->method('getStore')->willReturn($storeModel);
        $storeModel->expects($this->once())->method('getName')
            ->willReturn($rssData['entries']['description']['store']);
        $this->urlBuilder->expects($this->any())->method('getUrl')->willReturn($rssUrl);
        $this->urlBuilder->expects($this->once())->method('setScope')->willReturnSelf();
        $productModel->expects($this->any())->method('getStoreId')->willReturn(1);
        $productModel->expects($this->any())->method('getId')->willReturn(1);
        $productModel->expects($this->once())->method('getReviewId')->willReturn(1);
        $productModel->expects($this->any())->method('getNickName')->willReturn('Product Nick');
        $productModel->expects($this->any())->method('getName')
            ->willReturn($rssData['entries']['description']['name']);
        $productModel->expects($this->once())->method('getDetail')
            ->willReturn($rssData['entries']['description']['review']);
        $productModel->expects($this->once())->method('getTitle')
            ->willReturn($rssData['entries']['description']['summary']);
        $productModel->expects($this->any())->method('getProductUrl')
            ->willReturn('http://product.magento.com');
        $this->rss->expects($this->once())->method('getProductCollection')
            ->willReturn([$productModel]);

        $data = $this->block->getRssData();

        $this->assertEquals($rssData['title'], $data['title']);
        $this->assertEquals($rssData['description'], $data['description']);
        $this->assertEquals($rssData['link'], $data['link']);
        $this->assertEquals($rssData['charset'], $data['charset']);
        $this->assertEquals($rssData['entries']['title'], $data['entries'][0]['title']);
        $this->assertEquals($rssData['entries']['link'], $data['entries'][0]['link']);
        $this->assertStringContainsString(
            $rssData['entries']['description']['rss_url'],
            $data['entries'][0]['description']
        );
        $this->assertStringContainsString(
            $rssData['entries']['description']['name'],
            $data['entries'][0]['description']
        );
        $this->assertStringContainsString(
            $rssData['entries']['description']['summary'],
            $data['entries'][0]['description']
        );
        $this->assertStringContainsString(
            $rssData['entries']['description']['review'],
            $data['entries'][0]['description']
        );
        $this->assertStringContainsString(
            $rssData['entries']['description']['store'],
            $data['entries'][0]['description']
        );
    }

    /**
     * @return void
     */
    public function testGetCacheLifetime()
    {
        $this->assertEquals(0, $this->block->getCacheLifetime());
    }

    /**
     * @return void
     */
    public function testIsAllowed()
    {
        $this->assertTrue($this->block->isAllowed());
    }

    /**
     * @return void
     */
    public function testGetFeeds()
    {
        $this->assertEquals([], $this->block->getFeeds());
    }
}
