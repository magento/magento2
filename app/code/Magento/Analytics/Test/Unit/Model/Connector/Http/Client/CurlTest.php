<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\Http\Client;

use Magento\Analytics\Model\Connector\Http\ConverterInterface;
use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\ResponseFactory;

/**
 * A unit test for testing of the CURL HTTP client.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Analytics\Model\Connector\Http\Client\Curl
     */
    private $curl;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl|\PHPUnit\Framework\MockObject\MockObject
     */
    private $curlAdapterMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var ResponseFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $responseFactoryMock;

    /**
     * @var ConverterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $converterMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->curlAdapterMock = $this->getMockBuilder(
            \Magento\Framework\HTTP\Adapter\Curl::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->loggerMock = $this->getMockBuilder(
            \Psr\Log\LoggerInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $curlFactoryMock = $this->getMockBuilder(CurlFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $curlFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->curlAdapterMock);

        $this->responseFactoryMock = $this->getMockBuilder(
            ResponseFactory::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $this->converterMock = $this->createJsonConverter();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->curl = $objectManagerHelper->getObject(
            \Magento\Analytics\Model\Connector\Http\Client\Curl::class,
            [
                'curlFactory' => $curlFactoryMock,
                'responseFactory' => $this->responseFactoryMock,
                'converter' => $this->converterMock,
                'logger' => $this->loggerMock,
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
                    'method' => \Magento\Framework\HTTP\ZendClient::POST,
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
        $this->curlAdapterMock->expects($this->any())
            ->method('getErrno')
            ->willReturn(0);

        $this->responseFactoryMock->expects($this->any())
            ->method('create')
            ->with($responseString)
            ->willReturn($response);

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
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createJsonConverter()
    {
        $converterMock = $this->getMockBuilder(JsonConverter::class)
            ->setMethodsExcept(['getContentTypeHeader'])
            ->disableOriginalConstructor()
            ->getMock();
        $converterMock->expects($this->any())->method('toBody')->willReturnCallback(function ($value) {
            return json_encode($value);
        });
        return $converterMock;
    }
}
