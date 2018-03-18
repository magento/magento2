<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\Http\Client;

use Magento\Analytics\Model\Connector\Http\ConverterInterface;
use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Framework\HTTP\Adapter\CurlFactory;
<<<<<<< HEAD
use Magento\Framework\HTTP\ResponseFactory;
=======
>>>>>>> upstream/2.2-develop

/**
 * A unit test for testing of the CURL HTTP client.
 */
class CurlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Analytics\Model\Connector\Http\Client\Curl
     */
<<<<<<< HEAD
    private $curl;
=======
    private $subject;
>>>>>>> upstream/2.2-develop

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl|\PHPUnit_Framework_MockObject_MockObject
     */
<<<<<<< HEAD
    private $curlAdapterMock;
=======
    private $curlMock;
>>>>>>> upstream/2.2-develop

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
<<<<<<< HEAD
     * @var ResponseFactory|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var CurlFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $curlFactoryMock;

    /**
     * @var \Magento\Analytics\Model\Connector\Http\ResponseFactory|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> upstream/2.2-develop
     */
    private $responseFactoryMock;

    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converterMock;

    /**
<<<<<<< HEAD
=======
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
>>>>>>> upstream/2.2-develop
     * @return void
     */
    protected function setUp()
    {
<<<<<<< HEAD
        $this->curlAdapterMock = $this->getMockBuilder(
=======
        $this->curlMock = $this->getMockBuilder(
>>>>>>> upstream/2.2-develop
            \Magento\Framework\HTTP\Adapter\Curl::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->loggerMock = $this->getMockBuilder(
            \Psr\Log\LoggerInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();
<<<<<<< HEAD
        $curlFactoryMock = $this->getMockBuilder(CurlFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $curlFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->curlAdapterMock);

        $this->responseFactoryMock = $this->getMockBuilder(
            ResponseFactory::class
=======
        $this->curlFactoryMock = $this->getMockBuilder(CurlFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->curlFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->curlMock);

        $this->responseFactoryMock = $this->getMockBuilder(
            \Magento\Analytics\Model\Connector\Http\ResponseFactory::class
>>>>>>> upstream/2.2-develop
        )
        ->disableOriginalConstructor()
        ->getMock();
        $this->converterMock = $this->createJsonConverter();

<<<<<<< HEAD
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->curl = $objectManagerHelper->getObject(
            \Magento\Analytics\Model\Connector\Http\Client\Curl::class,
            [
                'curlFactory' => $curlFactoryMock,
=======
        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\Model\Connector\Http\Client\Curl::class,
            [
                'curlFactory' => $this->curlFactoryMock,
>>>>>>> upstream/2.2-develop
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
                    'headers' => [JsonConverter::CONTENT_TYPE_HEADER],
                    'method' => \Magento\Framework\HTTP\ZendClient::POST,
                ]
            ]
        ];
    }

    /**
     * @return void
     * @dataProvider getTestData
     */
    public function testRequestSuccess(array $data)
    {
        $responseString = 'This is response.';
        $response = new  \Zend_Http_Response(201, [], $responseString);
<<<<<<< HEAD
        $this->curlAdapterMock->expects($this->once())
=======
        $this->curlMock->expects($this->once())
>>>>>>> upstream/2.2-develop
            ->method('write')
            ->with(
                $data['method'],
                $data['url'],
                $data['version'],
                $data['headers'],
                json_encode($data['body'])
            );
<<<<<<< HEAD
        $this->curlAdapterMock->expects($this->once())
            ->method('read')
            ->willReturn($responseString);
        $this->curlAdapterMock->expects($this->any())
=======
        $this->curlMock->expects($this->once())
            ->method('read')
            ->willReturn($responseString);
        $this->curlMock->expects($this->any())
>>>>>>> upstream/2.2-develop
            ->method('getErrno')
            ->willReturn(0);

        $this->responseFactoryMock->expects($this->any())
            ->method('create')
            ->with($responseString)
            ->willReturn($response);

        $this->assertEquals(
            $response,
<<<<<<< HEAD
            $this->curl->request(
=======
            $this->subject->request(
>>>>>>> upstream/2.2-develop
                $data['method'],
                $data['url'],
                $data['body'],
                $data['headers'],
                $data['version']
            )
        );
    }

    /**
     * @return void
     * @dataProvider getTestData
     */
    public function testRequestError(array $data)
    {
        $response = new  \Zend_Http_Response(0, []);
<<<<<<< HEAD
        $this->curlAdapterMock->expects($this->once())
=======
        $this->curlMock->expects($this->once())
>>>>>>> upstream/2.2-develop
            ->method('write')
            ->with(
                $data['method'],
                $data['url'],
                $data['version'],
                $data['headers'],
                json_encode($data['body'])
            );
<<<<<<< HEAD
        $this->curlAdapterMock->expects($this->once())
            ->method('read');
        $this->curlAdapterMock->expects($this->atLeastOnce())
            ->method('getErrno')
            ->willReturn(1);
        $this->curlAdapterMock->expects($this->atLeastOnce())
=======
        $this->curlMock->expects($this->once())
            ->method('read');
        $this->curlMock->expects($this->atLeastOnce())
            ->method('getErrno')
            ->willReturn(1);
        $this->curlMock->expects($this->atLeastOnce())
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
            $this->curl->request(
=======
            $this->subject->request(
>>>>>>> upstream/2.2-develop
                $data['method'],
                $data['url'],
                $data['body'],
                $data['headers'],
                $data['version']
            )
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createJsonConverter()
    {
        $converterMock = $this->getMockBuilder(ConverterInterface::class)
            ->getMockForAbstractClass();
        $converterMock->expects($this->any())->method('toBody')->willReturnCallback(function ($value) {
            return json_encode($value);
        });
        $converterMock->expects($this->any())
            ->method('getContentTypeHeader')
            ->willReturn(JsonConverter::CONTENT_TYPE_HEADER);
        return $converterMock;
    }
}
