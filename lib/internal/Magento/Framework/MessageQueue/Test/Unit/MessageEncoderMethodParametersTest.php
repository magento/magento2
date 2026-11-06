<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Json\Encoder;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regression coverage for parameters named by a message topic's service signature.
 */
class MessageEncoderMethodParametersTest extends TestCase
{
    /**
     * @var MessageEncoder
     */
    private MessageEncoder $encoder;

    protected function setUp(): void
    {
        $communicationConfig = $this->createStub(CommunicationConfig::class);
        $communicationConfig->method('getTopic')->willReturn([
            CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_METHOD,
            CommunicationConfig::TOPIC_REQUEST => [
                ['param_name' => 'productId', 'param_position' => 0, 'is_required' => true, 'param_type' => 'int'],
                ['param_name' => 'saveOptions', 'param_position' => 1, 'is_required' => false, 'param_type' => 'bool'],
            ],
        ]);
        $dataObjectEncoder = $this->createStub(ServiceOutputProcessor::class);
        $dataObjectEncoder->method('convertValue')->willReturnArgument(0);
        $dataObjectDecoder = $this->createStub(ServiceInputProcessor::class);
        $dataObjectDecoder->method('convertValue')->willReturnArgument(0);
        $serializer = new Json();
        $this->encoder = new MessageEncoder(
            $this->createStub(QueueConfig::class),
            new Encoder($this->createStub(InlineInterface::class), $serializer),
            new Decoder($serializer),
            $dataObjectEncoder,
            $dataObjectDecoder
        );
        (new ObjectManager($this))->setBackwardCompatibleProperty(
            $this->encoder,
            'communicationConfig',
            $communicationConfig
        );
    }

    /**
     * Method parameters are normalized to their names in the service signature.
     */
    #[DataProvider('methodMessageProvider')]
    public function testEncodeMethodParameters(array $message, array $expected): void
    {
        self::assertSame($expected, json_decode($this->encoder->encode('product.updated', $message), true));
    }

    /**
     * The async consumer decodes REST parameter names using the same method schema.
     */
    #[DataProvider('methodMessageProvider')]
    public function testDecodeMethodParameters(array $message, array $expected): void
    {
        self::assertSame($expected, $this->encoder->decode('product.updated', json_encode($message)));
    }

    /**
     * @return array
     */
    public static function methodMessageProvider(): array
    {
        return [
            'camelCase' => [
                ['productId' => 42, 'saveOptions' => true],
                ['productId' => 42, 'saveOptions' => true],
            ],
            'snake_case' => [
                ['product_id' => 42, 'save_options' => true],
                ['productId' => 42, 'saveOptions' => true],
            ],
            'mixed parameter names' => [
                ['save_options' => false, 'productId' => 42],
                ['productId' => 42, 'saveOptions' => false],
            ],
            'camelCase takes precedence' => [
                ['productId' => 42, 'product_id' => 99, 'saveOptions' => false, 'save_options' => true],
                ['productId' => 42, 'saveOptions' => false],
            ],
            'camelCase takes precedence with snake_case first' => [
                ['product_id' => 99, 'productId' => 42, 'save_options' => true, 'saveOptions' => false],
                ['productId' => 42, 'saveOptions' => false],
            ],
            'indexed parameters' => [
                [42, false],
                ['productId' => 42, 'saveOptions' => false],
            ],
            'optional named parameter omitted' => [
                ['product_id' => 42],
                ['productId' => 42],
            ],
            'optional indexed parameter omitted' => [
                [42],
                ['productId' => 42],
            ],
            'zero and false are supplied values' => [
                ['product_id' => 0, 'save_options' => false],
                ['productId' => 0, 'saveOptions' => false],
            ],
            'zero camelCase value takes precedence' => [
                ['productId' => 0, 'product_id' => 99],
                ['productId' => 0],
            ],
            'null camelCase falls back to snake_case' => [
                ['productId' => null, 'product_id' => 42],
                ['productId' => 42],
            ],
        ];
    }

    /**
     * @param array $message
     */
    #[DataProvider('missingRequiredParameterProvider')]
    public function testEncodeMissingRequiredMethodParameter(array $message): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Data item corresponding to "productId" must be specified in the message with topic "product.updated".'
        );

        $this->encoder->encode('product.updated', $message);
    }

    /**
     * @param array $message
     */
    #[DataProvider('missingRequiredParameterProvider')]
    public function testDecodeMissingRequiredMethodParameter(array $message): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Data item corresponding to "productId" must be specified in the message with topic "product.updated".'
        );

        $this->encoder->decode('product.updated', json_encode($message));
    }

    /**
     * @return array
     */
    public static function missingRequiredParameterProvider(): array
    {
        return [
            'empty message' => [[]],
            'camelCase optional parameter only' => [['saveOptions' => false]],
            'snake_case optional parameter only' => [['save_options' => false]],
            'null camelCase required parameter' => [['productId' => null]],
            'null snake_case required parameter' => [['product_id' => null]],
            'both required parameter aliases are null' => [['productId' => null, 'product_id' => null]],
            'null indexed required parameter' => [[null, false]],
        ];
    }
}
