<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Model\Observer */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\Observer */
    protected $_observerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\HTTP\Adapter\Curl */
    protected $_curlMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\PageCache\Model\Config */
    protected $_configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\PageCache\Helper\Data */
    protected $_helperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Object\ */
    protected $_observerObject;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->_configMock = $this->getMock(
            'Magento\PageCache\Model\Config',
            ['getType', 'isEnabled'],
            [],
            '',
            false
        );
        $this->_helperMock = $this->getMock('Magento\PageCache\Helper\Data', ['getUrl'], [], '', false);
        $this->_curlMock = $this->getMock(
            '\Magento\Framework\HTTP\Adapter\Curl',
            ['setOptions', 'write', 'read', 'close'],
            [],
            '',
            false
        );
        $this->logger = $this->getMock('Magento\Framework\Cache\InvalidateLogger', [], [], '', false);
        $this->_model = new \Magento\CacheInvalidate\Model\Observer(
            $this->_configMock,
            $this->_helperMock,
            $this->_curlMock,
            $this->logger
        );
        $this->_observerMock = $this->getMock(
            'Magento\Framework\Event\Observer',
            ['getEvent'],
            [],
            '',
            false
        );
        $this->_observerObject = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
    }

    /**
     * Test case for cache invalidation
     */
    public function testInvalidateVarnish()
    {
        $tags = ['cache_1', 'cache_group'];
        $pattern = '((^|,)cache(,|$))|((^|,)cache_1(,|$))|((^|,)cache_group(,|$))';

        $this->_configMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\PageCache\Model\Config::VARNISH)
        );
        $eventMock = $this->getMock('Magento\Framework\Event', ['getObject'], [], '', false);
        $eventMock->expects($this->once())->method('getObject')->will($this->returnValue($this->_observerObject));
        $this->_observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
        $this->_observerObject->expects($this->once())->method('getIdentities')->will($this->returnValue($tags));
        $this->sendPurgeRequest($pattern);

        $this->_model->invalidateVarnish($this->_observerMock);
    }

    /**
     * Test case for flushing all the cache
     */
    public function testFlushAllCache()
    {
        $this->_configMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\PageCache\Model\Config::VARNISH)
        );

        $this->sendPurgeRequest('.*');
        $this->_model->flushAllCache($this->_observerMock);
    }

    /**
     * @param string $tags
     */
    protected function sendPurgeRequest($tags)
    {
        $url = 'http://mangento.index.php';
        $httpVersion = '1.1';
        $headers = ["X-Magento-Tags-Pattern: {$tags}"];
        $this->_helperMock->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('*'),
            []
        )->will(
            $this->returnValue($url)
        );
        $this->_curlMock->expects($this->once())->method('setOptions')->with([CURLOPT_CUSTOMREQUEST => 'PURGE']);
        $this->_curlMock->expects(
            $this->once()
        )->method(
            'write'
        )->with(
            $this->equalTo(''),
            $this->equalTo($url),
            $httpVersion,
            $this->equalTo($headers)
        );
        $this->_curlMock->expects($this->once())->method('read');
        $this->_curlMock->expects($this->once())->method('close');
    }
}
