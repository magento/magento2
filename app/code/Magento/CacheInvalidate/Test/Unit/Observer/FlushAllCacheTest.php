<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Observer;

class FlushAllCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Observer\FlushAllCache */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\Observer */
    protected $observerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\PageCache\Model\Config */
    protected $configMock;


    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Model\PurgeCache */
    protected $purgeCache;

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
        $this->model = new \Magento\CacheInvalidate\Observer\FlushAllCache(
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
    }

    /**
     * Test case for flushing all the cache
     */
    public function testFlushAllCache()
    {
        $this->configMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\PageCache\Model\Config::VARNISH)
        );

        $this->purgeCache->expects($this->once())->method('sendPurgeRequest')->with('.*');
        $this->model->invoke($this->observerMock);
    }
}
