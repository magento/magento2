<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rss\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RssTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rss\Model\Rss
     */
    protected $rss;

    /**
     * @var array
     */
    protected $feedData = [
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
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->cacheMock = $this->getMock(\Magento\Framework\App\CacheInterface::class);
        $this->serializerMock = $this->getMock(SerializerInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            \Magento\Rss\Model\Rss::class,
            [
                'cache' => $this->cacheMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetFeeds()
    {
        $dataProvider = $this->getMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->any())->method('getCacheKey')->will($this->returnValue('cache_key'));
        $dataProvider->expects($this->any())->method('getCacheLifetime')->will($this->returnValue(100));
        $dataProvider->expects($this->any())->method('getRssData')->will($this->returnValue($this->feedData));

        $this->rss->setDataProvider($dataProvider);

        $this->cacheMock->expects($this->once())->method('load')->will($this->returnValue(false));
        $this->cacheMock->expects($this->once())->method('save')->will($this->returnValue(true));
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->feedData)
            ->willReturn('serializedData');

        $this->assertEquals($this->feedData, $this->rss->getFeeds());
    }

    public function testGetFeedsWithCache()
    {
        $dataProvider = $this->getMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->any())->method('getCacheKey')->will($this->returnValue('cache_key'));
        $dataProvider->expects($this->any())->method('getCacheLifetime')->will($this->returnValue(100));
        $dataProvider->expects($this->never())->method('getRssData');

        $this->rss->setDataProvider($dataProvider);

        $this->cacheMock->expects($this->once())->method('load')
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
        $dataProvider = $this->getMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->any())->method('getCacheKey')->will($this->returnValue('cache_key'));
        $dataProvider->expects($this->any())->method('getCacheLifetime')->will($this->returnValue(100));
        $dataProvider->expects($this->any())->method('getRssData')->will($this->returnValue($this->feedData));

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
