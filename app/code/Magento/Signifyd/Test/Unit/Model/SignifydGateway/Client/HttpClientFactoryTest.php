<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\SignifydGateway\Client;

use Magento\Signifyd\Model\SignifydGateway\Client\HttpClientFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Signifyd\Model\Config;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Json\EncoderInterface;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class HttpClientFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private static $dummy = 'dummy';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ZendClientFactory|MockObject
     */
    private $clientFactory;

    /**
     * @var ZendClient|MockObject
     */
    private $client;

    /**
     * @var EncoderInterface|MockObject
     */
    private $dataEncoder;

    /**
     * @var ZendClient|MockObject
     */
    private $httpClient;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->client = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHeaders', 'setMethod', 'setUri', 'setRawData'])
            ->getMock();

        $this->clientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->clientFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->client);

        $this->dataEncoder = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClient = $this->objectManager->getObject(HttpClientFactory::class, [
            'config'        => $this->config,
            'clientFactory' => $this->clientFactory,
            'dataEncoder'   => $this->dataEncoder
        ]);
    }

    public function testCreateHttpClient()
    {
        $this->config->expects($this->once())
            ->method('getApiKey')
            ->willReturn('testKey');

        $this->config->expects($this->once())
            ->method('getApiUrl')
            ->willReturn('testUrl');

        $client = $this->httpClient->create('url', 'method');

        $this->assertInstanceOf(ZendClient::class, $client);
    }

    public function testCreateWithParams()
    {
        $param = ['id' => 1];
        $json = '{"id":1}';

        $this->config->expects($this->once())
            ->method('getApiKey')
            ->willReturn('testKey');

        $this->config->expects($this->once())
            ->method('getApiUrl')
            ->willReturn(self::$dummy);

        $this->dataEncoder->expects($this->once())
            ->method('encode')
            ->with($this->equalTo($param))
            ->willReturn($json);

        $this->client->expects($this->once())
            ->method('setRawData')
            ->with($this->equalTo($json), 'application/json')
            ->willReturnSelf();

        $client = $this->httpClient->create('url', 'method', $param);

        $this->assertInstanceOf(ZendClient::class, $client);
    }
}
