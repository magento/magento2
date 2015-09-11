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
        $this->uriMock = $this->getMock('\Zend\Uri\Uri', [], [], '', false);
        $this->socketAdapterMock = $this->getMock('\Zend\Http\Client\Adapter\Socket', [], [], '', false);
        $this->configMock = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Cache\InvalidateLogger', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->socketAdapterMock->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 10]);
        $this->model = new \Magento\CacheInvalidate\Model\Observer(
            $this->ConfigMock,
            $this->uriMock,
            $this->socketAdapterMock,
            $this->loggerMock,
            $this->configMock,
            $this->requestMock
        );
        $this->_observerMock = $this->getMock('Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
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

    public function testSendPurgeRequestEmptyConfig()
    {
        $this->socketAdapterMock->expects($this->once())
            ->method('write')
            ->with('PURGE', $this->uriMock, '1.1', $this->equalTo(['X-Magento-Tags-Pattern' => 'tags']));
        $this->socketAdapterMock->expects($this->once())
            ->method('close');
        $this->configMock->expects($this->once())
            ->method('get')
            ->willReturn('');
        $this->requestMock->expects($this->any())
            ->method('getHttpHost')
            ->willReturn('127.0.0.1');
        $this->uriMock->expects($this->once())
            ->method('setScheme')
            ->with('http')
            ->willReturnSelf();
        $this->uriMock->expects($this->once())
            ->method('setHost')
            ->with('127.0.0.1')
            ->willReturnSelf();
        $this->uriMock->expects($this->once())
            ->method('setPort')
            ->with(\Magento\CacheInvalidate\Model\PurgeCache::DEFAULT_PORT);
        $this->model->sendPurgeRequest('tags');
    }

    public function testSendPurgeRequestOneServer()
    {
        $this->socketAdapterMock->expects($this->once())
            ->method('write')
            ->with('PURGE', $this->uriMock, '1.1', $this->equalTo(['X-Magento-Tags-Pattern' => 'tags']));
        $this->socketAdapterMock->expects($this->once())
            ->method('close');
        $this->configMock->expects($this->once())
            ->method('get')
            ->willReturn([['host' => '127.0.0.2', 'port' => 1234]]);
        $this->uriMock->expects($this->once())
            ->method('setScheme')
            ->with('http')
            ->willReturnSelf();
        $this->uriMock->expects($this->once())
            ->method('setHost')
            ->with('127.0.0.2')
            ->willReturnSelf();
        $this->uriMock->expects($this->once())
            ->method('setPort')
            ->with(1234);
        $this->model->sendPurgeRequest('tags');
    }

    public function testSendPurgeRequestMultipleServers()
    {
        $this->socketAdapterMock->expects($this->exactly(2))
            ->method('write')
            ->with('PURGE', $this->uriMock, '1.1', $this->equalTo(['X-Magento-Tags-Pattern' => 'tags']));
        $this->socketAdapterMock->expects($this->exactly(2))
            ->method('close');
        $this->configMock->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    ['host' => '127.0.0.1', 'port' => 8080],
                    ['host' => '127.0.0.2', 'port' => 1234]
                ]
            );
        $this->uriMock->expects($this->at(0))
            ->method('setScheme')
            ->with('http')
            ->willReturnSelf();
        $this->uriMock->expects($this->at(1))
            ->method('setHost')
            ->with('127.0.0.1')
            ->willReturnSelf();
        $this->uriMock->expects($this->at(2))
            ->method('setPort')
            ->with(8080);
        $this->uriMock->expects($this->at(3))
            ->method('setScheme')
            ->with('http')
            ->willReturnSelf();
        $this->uriMock->expects($this->at(4))
            ->method('setHost')
            ->with('127.0.0.2')
            ->willReturnSelf();
        $this->uriMock->expects($this->at(5))
            ->method('setPort')
            ->with(1234);
        $this->model->sendPurgeRequest('tags');
    }
}
