<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\Http\Client;

use Magento\Framework\HTTP\Adapter\CurlFactory;

/**
 * A unit test for testing of the CURL HTTP client.
 */
class CurlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Analytics\Model\Connector\Http\Client\Curl
     */
    private $subject;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl|\PHPUnit_Framework_MockObject_MockObject
     */
    private $curlMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Zend_Http_Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var CurlFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $curlFactoryMock;

    /**
     * @var \Magento\Analytics\Model\Connector\Http\ResponseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->curlMock = $this->getMockBuilder(
            \Magento\Framework\HTTP\Adapter\Curl::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->loggerMock = $this->getMockBuilder(
            \Psr\Log\LoggerInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->responseMock = $this->getMockBuilder(
            \Zend_Http_Response::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->curlFactoryMock = $this->getMockBuilder(CurlFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->curlFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->curlMock);

        $this->responseFactoryMock = $this->getMockBuilder(
            \Magento\Analytics\Model\Connector\Http\ResponseFactory::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\Model\Connector\Http\Client\Curl::class,
            [
                'curlFactory' => $this->curlFactoryMock,
                'responseFactory' => $this->responseFactoryMock,
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
                    'body'=> '{"name": "value"}',
                    'url' => 'http://www.mystore.com',
                    'headers' => ['Content-Type: application/json'],
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

        $this->curlMock->expects($this->once())
            ->method('write')
            ->with(
                $data['method'],
                $data['url'],
                $data['version'],
                $data['headers'],
                $data['version']
            );
        $this->curlMock->expects($this->once())
            ->method('read')
            ->willReturn($responseString);
        $this->curlMock->expects($this->any())
            ->method('getErrno')
            ->willReturn(0);

        $this->responseFactoryMock->expects($this->any())
            ->method('create')
            ->with($responseString)
            ->willReturn($this->responseMock);

        $this->assertEquals(
            $this->responseMock,
            $this->subject->request(
                $data['method'],
                $data['url'],
                $data['version'],
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
        $this->curlMock->expects($this->once())
            ->method('write')
            ->with(
                $data['method'],
                $data['url'],
                $data['version'],
                $data['headers'],
                $data['version']
            );
        $this->curlMock->expects($this->once())
            ->method('read');
        $this->curlMock->expects($this->atLeastOnce())
            ->method('getErrno')
            ->willReturn(1);
        $this->curlMock->expects($this->atLeastOnce())
            ->method('getError')
            ->willReturn('CURL error.');

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with(
                new \Exception(
                    'MBI service CURL connection error #1: CURL error.'
                )
            );

        $this->assertFalse(
            $this->subject->request(
                $data['method'],
                $data['url'],
                $data['version'],
                $data['headers'],
                $data['version']
            )
        );
    }
}
