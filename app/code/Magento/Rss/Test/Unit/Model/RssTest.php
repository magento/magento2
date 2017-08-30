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
    <generator>Zend_Feed</generator>
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
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\App\FeedFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $feedFactoryMock;

    /**
     * @var \Magento\Framework\App\FeedInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $feedMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->cacheMock = $this->createMock(\Magento\Framework\App\CacheInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
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
        $dataProvider->expects($this->any())->method('getCacheKey')->will($this->returnValue('cache_key'));
        $dataProvider->expects($this->any())->method('getCacheLifetime')->will($this->returnValue(100));
        $dataProvider->expects($this->any())->method('getRssData')->will($this->returnValue($this->feedData));

        $this->rss->setDataProvider($dataProvider);

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('cache_key')
            ->will($this->returnValue(false));
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with('serializedData')
            ->will($this->returnValue(true));
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->feedData)
            ->willReturn('serializedData');

        $this->assertEquals($this->feedData, $this->rss->getFeeds());
    }

    public function testGetFeedsWithCache()
    {
        $dataProvider = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->any())->method('getCacheKey')->will($this->returnValue('cache_key'));
        $dataProvider->expects($this->any())->method('getCacheLifetime')->will($this->returnValue(100));
        $dataProvider->expects($this->never())->method('getRssData');

        $this->rss->setDataProvider($dataProvider);

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('cache_key')
            ->will($this->returnValue('serializedData'));
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
        $dataProvider->expects($this->any())->method('getCacheKey')->will($this->returnValue('cache_key'));
        $dataProvider->expects($this->any())->method('getCacheLifetime')->will($this->returnValue(100));
        $dataProvider->expects($this->any())->method('getRssData')->will($this->returnValue($this->feedData));

        $this->feedMock->expects($this->once())
            ->method('getFormatedContentAs')
            ->with(\Magento\Framework\App\FeedOutputFormatsInterface::DEFAULT_FORMAT)
            ->will($this->returnValue($this->feedXml));

        $this->feedFactoryMock->expects($this->once())
            ->method('importArray')
            ->with($this->feedData, \Magento\Framework\App\FeedFormatsInterface::DEFAULT_FORMAT)
            ->will($this->returnValue($this->feedMock));

        $this->rss->setDataProvider($dataProvider);
        $result = $this->rss->createRssXml();
        $this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $result);
        $this->assertContains('<title><![CDATA[Feed Title]]></title>', $result);
        $this->assertContains('<title><![CDATA[Feed 1 Title]]></title>', $result);
        $this->assertContains('<link>http://magento.com/rss/link</link>', $result);
        $this->assertContains('<link>http://magento.com/rss/link/id/1</link>', $result);
        $this->assertContains('<description><![CDATA[Feed Description]]></description>', $result);
        $this->assertContains('<description><![CDATA[Feed 1 Description]]></description>', $result);
    }
}
