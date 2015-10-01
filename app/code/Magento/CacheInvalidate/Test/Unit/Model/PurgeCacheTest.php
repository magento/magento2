<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

use \Zend\Uri\UriFactory;

class PurgeCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CacheInvalidate\Model\PurgeCache */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Zend\Http\Client\Adapter\Socket */
    protected $socketAdapterMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Cache\InvalidateLogger */
    protected $loggerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\DeploymentConfig */
    protected $configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface */
    protected $requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\UrlInterface */
    protected $urlBuilderMock;

    public function setUp()
    {
        $socketFactoryMock = $this->getMock('Magento\CacheInvalidate\Model\SocketFactory', [], [], '', false);
        $this->socketAdapterMock = $this->getMock('\Zend\Http\Client\Adapter\Socket', [], [], '', false);
        $this->socketAdapterMock->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 10]);
        $socketFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->socketAdapterMock);

        $this->configMock = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Cache\InvalidateLogger', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\CacheInvalidate\Model\PurgeCache',
            [
                'urlBuilder' => $this->urlBuilderMock,
                'socketAdapterFactory' => $socketFactoryMock,
                'logger' => $this->loggerMock,
                'config' => $this->configMock,
                'request' => $this->requestMock,
            ]
        );
    }

    /**
     * @param int $getHttpHostCallCtr
     * @param string $httpHost
     * @param int $getUrlCallCtr
     * @param string $url
     * @param string[] $hostConfig
     * @dataProvider sendPurgeRequestDataProvider
     */
    public function testSendPurgeRequest(
        $getHttpHostCallCtr,
        $httpHost,
        $getUrlCallCtr,
        $url,
        $hostConfig = null
    ) {
        $this->configMock->expects($this->once())
            ->method('get')
            ->willReturn($hostConfig);
        $this->requestMock->expects($this->exactly($getHttpHostCallCtr))
            ->method('getHttpHost')
            ->willReturn($httpHost);
        $this->urlBuilderMock->expects($this->exactly($getUrlCallCtr))
            ->method('getUrl')
            ->with('*')
            ->willReturn($url);

        if (null === $hostConfig) {
            $uri = null;
            if ($getHttpHostCallCtr > 0) {
                $uri = UriFactory::factory('')->setHost($httpHost)
                    ->setPort(\Magento\CacheInvalidate\Model\PurgeCache::DEFAULT_PORT)
                    ->setScheme('http');
            }
            if ($getUrlCallCtr > 0) {
                $uri = UriFactory::factory($url);
            }
            $this->socketAdapterMock->expects($this->once())
                ->method('connect')
                ->with($uri->getHost(), $uri->getPort());
            $this->socketAdapterMock->expects($this->once())
                ->method('write')
                ->with('PURGE', $uri, '1.1', ['X-Magento-Tags-Pattern' => 'tags']);
            $this->socketAdapterMock->expects($this->once())
                ->method('close');
        } else {
            $i = 1;
            foreach ($hostConfig as $host) {
                $port = isset($host['port']) ? $host['port'] : \Magento\CacheInvalidate\Model\PurgeCache::DEFAULT_PORT;
                $uri = UriFactory::factory('')->setHost($host['host'])
                    ->setPort($port)
                    ->setScheme('http');
                $this->socketAdapterMock->expects($this->at($i++))
                    ->method('connect')
                    ->with($uri->getHost(), $uri->getPort());
                $this->socketAdapterMock->expects($this->at($i++))
                    ->method('write')
                    ->with('PURGE', $uri, '1.1', ['X-Magento-Tags-Pattern' => 'tags']);
                $i++;
            }
            $this->socketAdapterMock->expects($this->exactly(count($hostConfig)))
                ->method('close');
        }
        $this->loggerMock->expects($this->once())
            ->method('execute');

        $this->assertTrue($this->model->sendPurgeRequest('tags'));
    }

    public function sendPurgeRequestDataProvider()
    {
        return [
            'http host' => [1, '127.0.0.1', 0, '',],
            'url' => [1, '', 1, 'http://host',],
            'config' => [
                0,
                '',
                0,
                '',
                [
                    ['host' => '127.0.0.1', 'port' => 8080],
                    ['host' => '127.0.0.2', 'port' => 1234],
                    ['host' => 'host']
                ]
            ]
        ];
    }

    public function testSendPurgeRequestWithException()
    {
        $this->configMock->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $this->requestMock->expects($this->once())
            ->method('getHttpHost')
            ->willReturn('httpHost');
        $this->socketAdapterMock->method('connect')
            ->willThrowException(new \Zend\Http\Client\Adapter\Exception\RuntimeException());
        $this->loggerMock->expects($this->never())
            ->method('execute');
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->model->sendPurgeRequest('tags'));
    }
}
