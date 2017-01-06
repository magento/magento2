<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\SignifydGateway\Response;

use Magento\Framework\Json\DecoderInterface;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequest;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookMessageReader;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookMessage;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookMessageFactory;

/**
 * Class WebhookMessageReaderTest
 */
class WebhookMessageReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebhookMessageReader
     */
    private $model;

    /**
     * @var DecoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decoder;

    /**
     * @var WebhookMessageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $webhookMessageFactory;

    /**
     * @var WebhookRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $webhookRequest;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->decoder = $this->getMockBuilder(DecoderInterface::class)
            ->getMockForAbstractClass();

        $this->webhookMessageFactory = $this->getMockBuilder(WebhookMessageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->webhookRequest = $this->getMockBuilder(WebhookRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new WebhookMessageReader(
            $this->decoder,
            $this->webhookMessageFactory
        );
    }

    /**
     * Tests successful reading webhook message from request.
     *
     */
    public function testReadSuccess()
    {
        $rawBody = 'body';
        $topic = 'topic';
        $decodedData = ['status' => "DISMISSED", 'orderId' => '19418'];

        $this->webhookRequest->expects($this->once())
            ->method('getBody')
            ->willReturn($rawBody);
        $this->webhookRequest->expects($this->once())
            ->method('getEventTopic')
            ->willReturn('topic');
        $this->decoder->expects($this->once())
            ->method('decode')
            ->with($rawBody)
            ->willReturn($decodedData);
        $webhookMessage = $this->getMockBuilder(WebhookMessage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->webhookMessageFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'data' => $decodedData,
                    'eventTopic' => $topic
                ]
            )
            ->willReturn($webhookMessage);

        $this->assertEquals(
            $webhookMessage,
            $this->model->read($this->webhookRequest)
        );
    }

    /**
     * Tests reading failure webhook message from request.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testReadFail()
    {
        $this->decoder->expects($this->once())
            ->method('decode')
            ->willThrowException(new \Exception('Error'));

        $this->model->read($this->webhookRequest);
    }
}
