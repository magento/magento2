<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\SignifydGateway\Response;

use Magento\Framework\Json\DecoderInterface;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequestValidator;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequest;

/**
 * Class WebhookRequestValidatorTest
 */
class WebhookRequestValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WebhookRequestValidator
     */
    private $model;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * @var DecoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $decoder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->decoder = $this->getMockBuilder(DecoderInterface::class)
            ->getMockForAbstractClass();

        $this->model = new WebhookRequestValidator(
            $this->config,
            $this->decoder
        );
    }

    /**
     * Tests successful cases.
     *
     * @param string $body
     * @param string $topic
     * @param string $hash
     * @param int$callConfigCount
     * @dataProvider validateSuccessDataProvider
     */
    public function testValidateSuccess($body, $topic, $hash, $callConfigCount)
    {
        $this->config->expects($this->exactly($callConfigCount))
            ->method('getApiKey')
            ->willReturn('GpFZZnxGgIxuI8BazSm3v6eGK');

        $this->decoder->expects($this->once())
            ->method('decode')
            ->with($body)
            ->willReturn(['status' => "DISMISSED", 'orderId' => '19418']);

        $webhookRequest = $this->createWebhookRequest($body, $topic, $hash);

        $this->assertTrue(
            $this->model->validate($webhookRequest)
        );
    }

    /**
     * @case 1. All data are correct, event topic has real value
     * @case 2. All data are correct, event topic has test value
     * @return array
     */
    public function validateSuccessDataProvider()
    {
        return [
            1 => [
                'body' => '{ status: "DISMISSED", orderId: "19418" }',
                'topic' => 'cases/creation',
                'hash' => 'KWR8Bzu3tinEpDviw1opWSMJGFqfpA79nNGp0TEYM6Q=',
                'callConfigCount' => 1
            ],
            2 => [
                'body' => '{ status: "DISMISSED", orderId: "19418" }',
                'topic' => 'cases/test',
                'hash' => '6npAahliNbzYo/Qi4+g+JeqPhLFgg19sIbuxDLmvobw=',
                'callConfigCount' => 0
            ]
        ];
    }

    /**
     * Case with wrong event topic
     *
     * @param string $topic
     * @dataProvider validationTopicFailsDataProvider
     */
    public function testValidationTopicFails($topic)
    {
        $body = '{ status: "DISMISSED", orderId: "19418" }';
        $hash = 'KWR8Bzu3tinEpDviw1opWSMJGFqfpA79nNGp0TEYM6Q=';

        $this->config->expects($this->never())
            ->method('getApiKey');

        $this->decoder->expects($this->never())
            ->method('decode');

        $webhookRequest = $this->createWebhookRequest($body, $topic, $hash);

        $this->assertFalse(
            $this->model->validate($webhookRequest),
            'Negative webhook event topic value validation fails'
        );
    }

    /**
     * @return array
     */
    public function validationTopicFailsDataProvider()
    {
        return [
            ['wrong topic' => 'bla-bla-topic'],
            ['empty topic' => '']
        ];
    }

    /**
     * Case with wrong webhook request body
     *
     * @param string $body
     * @dataProvider validationBodyFailsDataProvider
     */
    public function testValidationBodyFails($body)
    {
        $topic = 'cases/creation';
        $hash = 'KWR8Bzu3tinEpDviw1opWSMJGFqfpA79nNGp0TEYM6Q=';
        $webhookRequest = $this->createWebhookRequest($body, $topic, $hash);

        $this->config->expects($this->never())
            ->method('getApiKey');

        if (empty($body)) {
            $this->decoder->expects($this->once())
                ->method('decode')
                ->with($body)
                ->willReturn('');
        } else {
            $this->decoder->expects($this->once())
                ->method('decode')
                ->with($body)
                ->willThrowException(new \Exception('Error'));
        }

        $this->assertFalse(
            $this->model->validate($webhookRequest),
            'Negative webhook request body validation fails'
        );
    }

    /**
     * @return array
     */
    public function validationBodyFailsDataProvider()
    {
        return [
            ['Empty request body' => ''],
            ['Bad request body' => '{ bad data}']
        ];
    }

    /**
     * Case with wrong hash
     */
    public function testValidationHashFails()
    {
        $topic = 'cases/creation';
        $body = '{ status: "DISMISSED", orderId: "19418" }';
        $hash = 'wrong hash';
        $webhookRequest = $this->createWebhookRequest($body, $topic, $hash);

        $this->config->expects($this->once())
            ->method('getApiKey')
            ->willReturn('GpFZZnxGgIxuI8BazSm3v6eGK');

        $this->decoder->expects($this->once())
            ->method('decode')
            ->with($body)
            ->willReturn(['status' => "DISMISSED", 'orderId' => '19418']);

        $this->assertFalse(
            $this->model->validate($webhookRequest),
            'Negative webhook hash validation fails'
        );
    }

    /**
     * Returns mocked WebhookRequest
     *
     * @param string $body
     * @param string $topic
     * @param string $hash
     * @return WebhookRequest|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createWebhookRequest($body, $topic, $hash)
    {
        $webhookRequest = $this->getMockBuilder(WebhookRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webhookRequest->expects($this->once())
            ->method('getBody')
            ->willReturn($body);
        $webhookRequest->expects($this->once())
            ->method('getEventTopic')
            ->willReturn($topic);
        $webhookRequest->expects($this->once())
            ->method('getHash')
            ->willReturn($hash);

        return $webhookRequest;
    }
}
