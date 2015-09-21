<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CacheInvalidate\Model\Observer */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Model\Observer */
    protected $modelMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\Observer */
    protected $observerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\PageCache\Model\Config */
    protected $configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Cache\InvalidateLogger */
    protected $loggerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Object\ */
    protected $observerObject;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Zend\Uri\Uri */
    protected $uriMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Zend\Http\Client\Adapter\Socket */
    protected $socketAdapterMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\DeploymentConfig */
    protected $deploymentConfigMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface */
    protected $requestMock;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->configMock = $this->getMock('Magento\PageCache\Model\Config', [], [], '', false);
        $this->uriFactoryMock = $this->getMock('Magento\CacheInvalidate\Model\UriFactory', [], [], '', false);
        $this->uriMock = $this->getMock('\Zend\Uri\Uri', [], [], '', false);
        $this->socketFactoryMock = $this->getMock('Magento\CacheInvalidate\Model\SocketFactory', [], [], '', false);
        $this->socketAdapterMock = $this->getMock('\Zend\Http\Client\Adapter\Socket', [], [], '', false);
        $this->deploymentConfigMock = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Cache\InvalidateLogger', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $this->observerObject = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->modelMock = $this->getMock(
            '\Magento\CacheInvalidate\Model\Observer',
            ['sendPurgeRequest'],
            [
                $this->configMock,
                $this->uriFactoryMock,
                $this->socketFactoryMock,
                $this->loggerMock,
                $this->deploymentConfigMock,
                $this->requestMock
            ]
        );
        $this->model = new \Magento\CacheInvalidate\Model\Observer(
            $this->configMock,
            $this->uriFactoryMock,
            $this->socketFactoryMock,
            $this->loggerMock,
            $this->deploymentConfigMock,
            $this->requestMock
        );
    }

    /**
     * Test case for cache invalidation
     */
    public function testInvalidateVarnish()
    {
        $tags = ['cache_1', 'cache_group'];
        $pattern = '((^|,)cache(,|$))|((^|,)cache_1(,|$))|((^|,)cache_group(,|$))';
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getType')
            ->willReturn(\Magento\PageCache\Model\Config::VARNISH);
        $eventMock = $this->getMock('Magento\Framework\Event', ['getObject'], [], '', false);
        $eventMock->expects($this->once())
            ->method('getObject')
            ->willReturn($this->observerObject);
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $this->observerObject->expects($this->once())
            ->method('getIdentities')
            ->willReturn($tags);
        $this->modelMock->expects($this->once())
            ->method('sendPurgeRequest')
            ->with($pattern);
        $this->modelMock->invalidateVarnish($this->observerMock);
    }

    /**
     * Test case for flushing all the cache
     */
    public function testFlushAllCache()
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getType')
            ->willReturn(\Magento\PageCache\Model\Config::VARNISH);
        $this->modelMock->expects($this->once())
            ->method('sendPurgeRequest')
            ->with('.*');
        $this->modelMock->flushAllCache($this->observerMock);
    }

    public function testSendPurgeRequestEmptyConfig()
    {
        $this->uriFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->uriMock);
        $this->socketFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->socketAdapterMock);
        $this->socketAdapterMock->expects($this->once())
            ->method('write')
            ->with('PURGE', $this->uriMock, '1.1', $this->equalTo(['X-Magento-Tags-Pattern' => 'tags']));
        $this->socketAdapterMock->expects($this->once())
            ->method('close');
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturn('');
        $this->requestMock->expects($this->any())
            ->method('getHttpHost')
            ->willReturn('127.0.0.1');
        $this->socketAdapterMock->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 10]);
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
            ->with(\Magento\CacheInvalidate\Model\Observer::DEFAULT_PORT);
        $method = new \ReflectionMethod($this->model, 'sendPurgeRequest');
        $method->setAccessible(true);
        $method->invoke($this->model, 'tags');
    }

    public function testSendPurgeRequestOneServer()
    {
        $this->uriFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->uriMock);
        $this->socketFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->socketAdapterMock);
        $this->socketAdapterMock->expects($this->once())
            ->method('write')
            ->with('PURGE', $this->uriMock, '1.1', $this->equalTo(['X-Magento-Tags-Pattern' => 'tags']));
        $this->socketAdapterMock->expects($this->once())
            ->method('close');
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturn([['host' => '127.0.0.2', 'port' => 1234]]);
        $this->socketAdapterMock->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 10]);
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
        $method = new \ReflectionMethod($this->model, 'sendPurgeRequest');
        $method->setAccessible(true);
        $method->invoke($this->model, 'tags');
    }

    public function testSendPurgeRequestMultipleServers()
    {
        $this->uriFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->uriMock);
        $this->socketFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->socketAdapterMock);
        $this->socketAdapterMock->expects($this->exactly(2))
            ->method('write')
            ->with('PURGE', $this->uriMock, '1.1', $this->equalTo(['X-Magento-Tags-Pattern' => 'tags']));
        $this->socketAdapterMock->expects($this->exactly(2))
            ->method('close');
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    ['host' => '127.0.0.1', 'port' => 8080],
                    ['host' => '127.0.0.2', 'port' => 1234]
                ]
            );
        $this->socketAdapterMock->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 10]);
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
        $method = new \ReflectionMethod($this->model, 'sendPurgeRequest');
        $method->setAccessible(true);
        $method->invoke($this->model, 'tags');
    }
}
