<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class InvalidateVarnishObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\Observer */
    protected $observerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\PageCache\Model\Config */
    protected $configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Model\PurgeCache */
    protected $purgeCache;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DataObject\ */
    protected $observerObject;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Cache\Tag\Resolver */
    private $tagResolver;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configMock = $this->getMock(
            \Magento\PageCache\Model\Config::class,
            ['getType', 'isEnabled'],
            [],
            '',
            false
        );
        $this->purgeCache = $this->getMock(\Magento\CacheInvalidate\Model\PurgeCache::class, [], [], '', false);
        $this->model = new \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver(
            $this->configMock,
            $this->purgeCache
        );

        $this->tagResolver = $this->getMock(\Magento\Framework\App\Cache\Tag\Resolver::class, [], [], '', false);
        $helper->setBackwardCompatibleProperty($this->model, 'tagResolver', $this->tagResolver);

        $this->observerMock = $this->getMock(
            \Magento\Framework\Event\Observer::class,
            ['getEvent'],
            [],
            '',
            false
        );
        $this->observerObject = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
    }

    /**
     * Test case for cache invalidation
     */
    public function testInvalidateVarnish()
    {
        $tags = ['cache_1', 'cache_group'];
        $pattern = '((^|,)cache_1(,|$))|((^|,)cache_group(,|$))';

        $this->configMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\PageCache\Model\Config::VARNISH)
        );

        $eventMock = $this->getMock(\Magento\Framework\Event::class, ['getObject'], [], '', false);
        $eventMock->expects($this->once())->method('getObject')->will($this->returnValue($this->observerObject));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
        $this->tagResolver->expects($this->once())->method('getTags')->with($this->observerObject)
            ->will($this->returnValue($tags));
        $this->purgeCache->expects($this->once())->method('sendPurgeRequest')->with($pattern);

        $this->model->execute($this->observerMock);
    }
}
