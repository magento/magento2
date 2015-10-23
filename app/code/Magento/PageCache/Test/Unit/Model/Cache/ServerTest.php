<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model\Cache;

use \Zend\Uri\UriFactory;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Model\Cache\Server */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\DeploymentConfig */
    protected $configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface */
    protected $requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\UrlInterface */
    protected $urlBuilderMock;

    public function setUp()
    {
        $this->configMock = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Cache\InvalidateLogger', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\PageCache\Model\Cache\Server',
            [
                'urlBuilder' => $this->urlBuilderMock,
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
     * @dataProvider getUrisDataProvider
     */
    public function testGetUris(
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
            ->with('*', ['_nosid' => true])
            ->willReturn($url);

        $uris = [];
        if (null === $hostConfig) {
            if (!empty($httpHost)) {
                $uris[] = UriFactory::factory('')->setHost($httpHost)
                    ->setPort(\Magento\PageCache\Model\Cache\Server::DEFAULT_PORT)
                    ->setScheme('http');
            }
            if (!empty($url)) {
                $uris[] = UriFactory::factory($url);
            }
        } else {
            foreach ($hostConfig as $host) {
                $port = isset($host['port']) ? $host['port'] : \Magento\PageCache\Model\Cache\Server::DEFAULT_PORT;
                $uris[] = UriFactory::factory('')->setHost($host['host'])
                    ->setPort($port)
                    ->setScheme('http');
            }
        }

        $this->assertEquals($uris, $this->model->getUris());
    }

    public function getUrisDataProvider()
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
}
