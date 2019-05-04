<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\SignifydGateway\Client;

use Magento\Signifyd\Model\SignifydGateway\Client\ResponseHandler;
use Magento\Framework\Json\DecoderInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiCallException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Zend_Http_Response as Response;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class ResponseHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private static $errorMessage = 'Some error';

    /**
     * @var string
     */
    private static $testJson = '{"id": 1}';

    /**
     * @var int
     */
    private static $successfulCode = 200;

    /**
     * @var int
     */
    private static $phpVersionId = 50000;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ResponseHandler|MockObject
     */
    private $responseHandler;

    /**
     * @var DecoderInterface|MockObject
     */
    private $dataDecoder;

    /**
     * @var Response|MockObject
     */
    private $response;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->dataDecoder = $this->getMockBuilder(DecoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getBody'])
            ->getMock();

        $this->responseHandler = $this->objectManager->getObject(ResponseHandler::class, [
            'dataDecoder' => $this->dataDecoder
        ]);
    }

    /**
     * @dataProvider errorsProvider
     */
    public function testHandleFailureMessage($code, $message)
    {
        $this->response->expects($this->any())
            ->method('getStatus')
            ->willReturn($code);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn(self::$errorMessage);

        try {
            $this->responseHandler->handle($this->response);
        } catch (ApiCallException $e) {
            $this->assertEquals($e->getMessage(), sprintf($message, self::$errorMessage));
        }
    }

    /**
     * @return array
     */
    public function errorsProvider()
    {
        return [
            [400, 'Bad Request - The request could not be parsed. Response: %s'],
            [401, 'Unauthorized - user is not logged in, could not be authenticated. Response: %s'],
            [403, 'Forbidden - Cannot access resource. Response: %s'],
            [404, 'Not Found - resource does not exist. Response: %s'],
            [
                409,
                'Conflict - with state of the resource on server. Can occur with (too rapid) PUT requests. Response: %s'
            ],
            [500, 'Server error. Response: %s']
        ];
    }

    /**
     * @expectedException     \Magento\Signifyd\Model\SignifydGateway\ApiCallException
     * @expectedExceptionMessage  Response is not valid JSON: Decoding failed: Syntax error
     */
    public function testHandleEmptyJsonException()
    {
        $this->response->expects($this->any())
            ->method('getStatus')
            ->willReturn(self::$successfulCode);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn('');

        $r = new \ReflectionObject($this->responseHandler);
        $prop = $r->getProperty('phpVersionId');
        $prop->setAccessible(true);
        $prop->setValue(self::$phpVersionId);

        $this->responseHandler->handle($this->response);
    }

    /**
     * @expectedException     \Magento\Signifyd\Model\SignifydGateway\ApiCallException
     * @expectedExceptionMessage  Response is not valid JSON: Some error
     */
    public function testHandleInvalidJson()
    {
        $this->response->expects($this->any())
            ->method('getStatus')
            ->willReturn(self::$successfulCode);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn('param');

        $this->dataDecoder = $this->getMockBuilder(DecoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataDecoder->expects($this->once())
            ->method('decode')
            ->with('param')
            ->willThrowException(new \Exception(self::$errorMessage, 30));

        $this->responseHandler = $this->objectManager->getObject(ResponseHandler::class, [
            'dataDecoder' => $this->dataDecoder
        ]);

        $this->responseHandler->handle($this->response);
    }

    public function testHandle()
    {
        $this->response->expects($this->any())
            ->method('getStatus')
            ->willReturn(self::$successfulCode);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn(self::$testJson);

        $this->dataDecoder->expects($this->once())
            ->method('decode')
            ->willReturn(json_decode(self::$testJson, 1));

        $decodedResponseBody = $this->responseHandler->handle($this->response);
        $this->assertEquals($decodedResponseBody, ['id' => 1]);
    }
}
