<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Observer;

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

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->configMock = $this->getMock(
            'Magento\PageCache\Model\Config',
            ['getType', 'isEnabled'],
            [],
            '',
            false
        );
        $this->purgeCache = $this->getMock('Magento\CacheInvalidate\Model\PurgeCache', [], [], '', false);
        $this->model = new \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver(
            $this->configMock,
            $this->purgeCache
        );
        $this->observerMock = $this->getMock(
            'Magento\Framework\Event\Observer',
            ['getEvent'],
            [],
            '',
            false
        );
        $this->observerObject = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
    }

    /**
     * Test case for cache invalidation
     */
    public function testInvalidateVarnish()
    {
        $tags = ['cache_1', 'cache_group'];
        $pattern = '((^|,)cache(,|$))|((^|,)cache_1(,|$))|((^|,)cache_group(,|$))';

        $this->configMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\PageCache\Model\Config::VARNISH)
        );
        $eventMock = $this->getMock('Magento\Framework\Event', ['getObject'], [], '', false);
        $eventMock->expects($this->once())->method('getObject')->will($this->returnValue($this->observerObject));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
        $this->observerObject->expects($this->once())->method('getIdentities')->will($this->returnValue($tags));
        $this->purgeCache->expects($this->once())->method('sendPurgeRequest')->with($pattern);

        $this->model->execute($this->observerMock);
    }
}
