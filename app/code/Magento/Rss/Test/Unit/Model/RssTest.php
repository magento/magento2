<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Test\Unit\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\FeedFactoryInterface;
use Magento\Framework\App\FeedInterface;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Rss\Model\Rss;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RssTest extends TestCase
{
    /**
     * @var Rss
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
    <generator>Laminas\Feed</generator>
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
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var FeedFactoryInterface|MockObject
     */
    private $feedFactoryMock;

    /**
     * @var FeedInterface|MockObject
     */
    private $feedMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->feedFactoryMock = $this->getMockForAbstractClass(FeedFactoryInterface::class);
        $this->feedMock = $this->getMockForAbstractClass(FeedInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            Rss::class,
            [
                'cache' => $this->cacheMock,
                'feedFactory' => $this->feedFactoryMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetFeeds()
    {
        $dataProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
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
        $dataProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
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
        $dataProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $dataProvider->expects($this->any())->method('getCacheKey')->willReturn('cache_key');
        $dataProvider->expects($this->any())->method('getCacheLifetime')->willReturn(100);
        $dataProvider->expects($this->any())->method('getRssData')->willReturn($this->feedData);

        $this->feedMock->expects($this->once())
            ->method('getFormattedContent')
            ->willReturn($this->feedXml);

        $this->feedFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->feedData, FeedFactoryInterface::FORMAT_RSS)
            ->willReturn($this->feedMock);

        $this->rss->setDataProvider($dataProvider);
        $this->assertNotNull($this->rss->createRssXml());
    }
}
