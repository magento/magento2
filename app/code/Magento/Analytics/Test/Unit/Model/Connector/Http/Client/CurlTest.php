<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector\Http\Client;

use Magento\Analytics\Model\Connector\Http\Client\Curl;
use Magento\Analytics\Model\Connector\Http\ConverterInterface;
use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\ResponseFactory;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * A unit test for testing of the CURL HTTP client.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurlTest extends TestCase
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl|MockObject
     */
    private $curlAdapterMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ResponseFactory|MockObject
     */
    private $responseFactoryMock;

    /**
     * @var ConverterInterface|MockObject
     */
    private $converterMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->curlAdapterMock = $this->createMock(\Magento\Framework\HTTP\Adapter\Curl::class);

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $curlFactoryMock = $this->getMockBuilder(CurlFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $curlFactoryMock
            ->method('create')
            ->willReturn($this->curlAdapterMock);

        $this->responseFactoryMock = $this->createMock(ResponseFactory::class);
        $this->converterMock = $this->createJsonConverter();

        $objectManagerHelper = new ObjectManager($this);

        $this->curl = $objectManagerHelper->getObject(
            Curl::class,
            [
                'curlFactory' => $curlFactoryMock,
                'responseFactory' => $this->responseFactoryMock,
                'converter' => $this->converterMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Returns test parameters for request.
     *
     * @return array
     */
    public function getTestData()
    {
        return [
            [
                'data' => [
                    'version' => '1.1',
                    'body'=> ['name' => 'value'],
                    'url' => 'http://www.mystore.com',
                    'method' => ZendClient::POST
                ]
            ]
        ];
    }

    /**
     * @param array $data
     * @return void
     * @throws \Zend_Http_Exception
     * @dataProvider getTestData
     */
    public function testRequestSuccess(array $data)
    {
        $responseString = 'This is response.';
        $response = new  \Zend_Http_Response(201, [], $responseString);
        $this->curlAdapterMock->expects($this->once())
            ->method('write')
            ->with(
                $data['method'],
                $data['url'],
                $data['version'],
                [$this->converterMock->getContentTypeHeader()],
                json_encode($data['body'])
            );
        $this->curlAdapterMock->expects($this->once())
            ->method('read')
            ->willReturn($responseString);
        $this->curlAdapterMock->method('getErrno')->willReturn(0);
        $this->responseFactoryMock->method('create')->with($responseString)->willReturn($response);

        $this->assertEquals(
            $response,
            $this->curl->request(
                $data['method'],
                $data['url'],
                $data['body'],
                [$this->converterMock->getContentTypeHeader()],
                $data['version']
            )
        );
    }

    /**
     * @param array $data
     * @return void
     * @throws \Zend_Http_Exception
     * @dataProvider getTestData
     */
    public function testRequestError(array $data)
    {
        $response = new  \Zend_Http_Response(0, []);
        $this->curlAdapterMock->expects($this->once())
            ->method('write')
            ->with(
                $data['method'],
                $data['url'],
                $data['version'],
                [$this->converterMock->getContentTypeHeader()],
                json_encode($data['body'])
            );
        $this->curlAdapterMock->expects($this->once())
            ->method('read');
        $this->curlAdapterMock->expects($this->atLeastOnce())
            ->method('getErrno')
            ->willReturn(1);
        $this->curlAdapterMock->expects($this->atLeastOnce())
            ->method('getError')
            ->willReturn('CURL error.');

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with(
                new \Exception(
                    'MBI service CURL connection error #1: CURL error.'
                )
            );

        $this->assertEquals(
            $response,
            $this->curl->request(
                $data['method'],
                $data['url'],
                $data['body'],
                [$this->converterMock->getContentTypeHeader()],
                $data['version']
            )
        );
    }

    /**
     * @return MockObject
     */
    private function createJsonConverter()
    {
        $converterMock = $this->getMockBuilder(JsonConverter::class)
            ->onlyMethods(['getContentTypeHeader','toBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $converterMock->method('toBody')->willReturnCallback(function ($value) {
            return json_encode($value);
        });
        return $converterMock;
    }
}
