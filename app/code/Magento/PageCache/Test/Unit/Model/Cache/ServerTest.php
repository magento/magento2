<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\Cache;

use Laminas\Uri\UriFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\PageCache\Model\Cache\Server;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    /** @var Server */
    protected $model;

    /** @var MockObject|DeploymentConfig */
    protected $configMock;

    /** @var MockObject|RequestInterface */
    protected $requestMock;

    /** @var MockObject|UrlInterface */
    protected $urlBuilderMock;

    /** @var MockObject|InvalidateLogger */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(DeploymentConfig::class);
        $this->loggerMock = $this->createMock(InvalidateLogger::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Server::class,
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

    /**
     * @return array
     */
    public static function getUrisDataProvider()
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
