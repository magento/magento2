<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

use \Magento\Framework\Config\ConfigOptionsListConstants;

class PurgeCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Model\PurgeCache */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Zend\Uri\Uri */
    protected $uriMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Zend\Http\Client\Adapter\Socket */
    protected $socketAdapterMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Cache\InvalidateLogger */
    protected $loggerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\DeploymentConfig\Reader */
    protected $configReaderMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface */
    protected $requestMock;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->uriMock = $this->getMock('\Zend\Uri\Uri', [], [], '', false);
        $this->socketAdapterMock = $this->getMock('\Zend\Http\Client\Adapter\Socket', [], [], '', false);
        $this->configMock = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Cache\InvalidateLogger', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->socketAdapterMock->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 10]);
        $this->model = new \Magento\CacheInvalidate\Model\PurgeCache(
            $this->uriMock,
            $this->socketAdapterMock,
            $this->loggerMock,
            $this->config,
            $this->requestMock
        );
    }

    public function testSendPurgeRequestEmptyConfig()
    {
        $this->socketAdapterMock->expects($this->once())
            ->method('write')
            ->with('PURGE', $this->uriMock, '1.1', $this->equalTo(['X-Magento-Tags-Pattern' => 'tags']));
        $this->socketAdapterMock->expects($this->once())
            ->method('close');
        $this->config->expects($this->once())
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
        $this->config->expects($this->once())
            ->method('get')
            ->willReturn(
                [ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS => [['host' => '127.0.0.2', 'port' => 1234]]]
            );
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
        $this->config->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS => [
                        ['host' => '127.0.0.1', 'port' => 8080],
                        ['host' => '127.0.0.2', 'port' => 1234]
                    ]
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
