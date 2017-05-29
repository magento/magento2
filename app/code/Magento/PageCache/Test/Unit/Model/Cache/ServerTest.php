<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model\Cache;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\PageCache\Model\Cache\Server;
use \Zend\Uri\UriFactory;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Server */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\DeploymentConfig */
    protected $configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface */
    protected $requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\UrlInterface */
    protected $urlBuilderMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $this->loggerMock = $this->getMock(\Magento\Framework\Cache\InvalidateLogger::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\PageCache\Model\Cache\Server::class,
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
        $this->configMock->expects($this->once())->method('get')->willReturn($hostConfig);
        $this->requestMock->expects($this->exactly($getHttpHostCallCtr))->method('getHttpHost')->willReturn($httpHost);

        $this->urlBuilderMock->expects($this->exactly($getUrlCallCtr))
            ->method('getUrl')
            ->with('*', ['_nosid' => true])
            ->willReturn($url);

        $uris = [];
        if (null === $hostConfig) {
            if (!empty($httpHost)) {
                $uris[] = UriFactory::factory('')->setHost($httpHost)->setPort(Server::DEFAULT_PORT);
            }
            if (!empty($url)) {
                $uris[] = UriFactory::factory($url);
            }
        } else {
            foreach ($hostConfig as $host) {
                $port = isset($host['port']) ? $host['port'] : Server::DEFAULT_PORT;
                $uris[] = UriFactory::factory('')->setHost($host['host'])->setPort($port);
            }
        }

        foreach (array_keys($uris) as $key) {
            $uris[$key]->setScheme('http')
                ->setPath('/')
                ->setQuery(null);
        }

        $this->assertEquals($uris, $this->model->getUris());
    }

    public function getUrisDataProvider()
    {
        return [
            'http host' => [2, '127.0.0.1', 0, ''],
            'url' => [1, '', 1, 'http://host'],
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
