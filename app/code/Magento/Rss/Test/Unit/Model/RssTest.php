<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rss\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RssTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Rss\Model\Rss
     */
    protected $rss;

    /**
     * @var array
     */
    private $feedData = [
        'title' => 'Feed Title',
        'link' => 'http://magento.com/rss/link',
        'description' => 'Feed Description',
        'charset' => 'UTF-8',
        'entries' => [
            [
                'title' => 'Feed 1 Title',
                'link' => 'http://magento.com/rss/link/id/1',
                'description' => 'Feed 1 Description',
            ],
        ],
    ];

    /**
     * @var string
     */
    private $feedXml = '<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">
  <channel>
    <title><![CDATA[Feed Title]]></title>
    <link>http://magento.com/rss/link</link>
    <description><![CDATA[Feed Description]]></description>
    <pubDate>Sat, 22 Apr 2017 13:21:12 +0200</pubDate>
    <generator>Zend\Feed</generator>
    <docs>http://blogs.law.harvard.edu/tech/rss</docs>
    <item>
      <title><![CDATA[Feed 1 Title]]></title>
      <link>http://magento.com/rss/link/id/1</link>
      <description><![CDATA[Feed 1 Description]]></description>
      <pubDate>Sat, 22 Apr 2017 13:21:12 +0200</pubDate>
    </item>
  </channel>
</rss>';

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\App\FeedFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $feedFactoryMock;

    /**
     * @var \Magento\Framework\App\FeedInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $feedMock;

    /**
     * @var SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(\Magento\Framework\App\CacheInterface::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->feedFactoryMock = $this->createMock(\Magento\Framework\App\FeedFactoryInterface::class);
        $this->feedMock = $this->createMock(\Magento\Framework\App\FeedInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            \Magento\Rss\Model\Rss::class,
            [
                'cache' => $this->cacheMock,
                'feedFactory' => $this->feedFactoryMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetFeeds()
    {
        $dataProvider = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->any())->method('getCacheKey')->willReturn('cache_key');
        $dataProvider->expects($this->any())->method('getCacheLifetime')->willReturn(100);
        $dataProvider->expects($this->any())->method('getRssData')->willReturn($this->feedData);

        $this->rss->setDataProvider($dataProvider);

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('cache_key')
            ->willReturn(false);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with('serializedData')
            ->willReturn(true);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->feedData)
            ->willReturn('serializedData');

        $this->assertEquals($this->feedData, $this->rss->getFeeds());
    }

    public function testGetFeedsWithCache()
    {
        $dataProvider = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->any())->method('getCacheKey')->willReturn('cache_key');
        $dataProvider->expects($this->any())->method('getCacheLifetime')->willReturn(100);
        $dataProvider->expects($this->never())->method('getRssData');

        $this->rss->setDataProvider($dataProvider);

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('cache_key')
            ->willReturn('serializedData');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($this->feedData);
        $this->cacheMock->expects($this->never())->method('save');

        $this->assertEquals($this->feedData, $this->rss->getFeeds());
    }

    public function testCreateRssXml()
    {
        $dataProvider = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->any())->method('getCacheKey')->willReturn('cache_key');
        $dataProvider->expects($this->any())->method('getCacheLifetime')->willReturn(100);
        $dataProvider->expects($this->any())->method('getRssData')->willReturn($this->feedData);

        $this->feedMock->expects($this->once())
            ->method('getFormattedContent')
            ->willReturn($this->feedXml);

        $this->feedFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->feedData, \Magento\Framework\App\FeedFactoryInterface::FORMAT_RSS)
            ->willReturn($this->feedMock);

        $this->rss->setDataProvider($dataProvider);
        $this->assertNotNull($this->rss->createRssXml());
    }
}
