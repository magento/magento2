<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit;

use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\ValueSuffixPreprocessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Custom attribute preprocessors in the Web API service input processor
 */
class ServiceInputProcessorCustomAttributePreprocessorTest extends TestCase
{
    private const ATTRIBUTE_CODE = 'manufacturer';

    /**
     * @var ValueSuffixPreprocessor
     */
    private $preprocessor;

    /**
     * @var ServiceInputProcessor
     */
    private $serviceInputProcessor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $attributeValueFactory = $this->createMock(AttributeValueFactory::class);
        $attributeValueFactory->method('create')
            ->willReturnCallback(static fn () => new AttributeValue());
        $this->preprocessor = new ValueSuffixPreprocessor(self::ATTRIBUTE_CODE);
        $this->serviceInputProcessor = $objectManager->getObject(
            ServiceInputProcessor::class,
            [
                'typeProcessor' => $objectManager->getObject(TypeProcessor::class),
                'attributeValueFactory' => $attributeValueFactory,
                'customAttributePreprocessors' => [$this->preprocessor],
            ]
        );
    }

    /**
     * @param array $customAttributes
     * @param string $expectedCode
     * @param string $expectedValue
     * @param string[] $expectedProcessedCodes
     */
    #[DataProvider('customAttributesDataProvider')]
    public function testPreprocessorsRunForAttributeCode(
        array $customAttributes,
        string $expectedCode,
        string $expectedValue,
        array $expectedProcessedCodes
    ): void {
        $result = $this->convert($customAttributes);

        $this->assertSame([$expectedCode], array_keys($result));
        $this->assertSame($expectedCode, $result[$expectedCode]->getAttributeCode());
        $this->assertSame($expectedValue, $result[$expectedCode]->getValue());
        $this->assertSame($expectedProcessedCodes, $this->preprocessor->getProcessedCodes());
    }

    /**
     * @return array
     */
    public static function customAttributesDataProvider(): array
    {
        $code = self::ATTRIBUTE_CODE;

        return [
            'list, snake case code' => [
                [['attribute_code' => $code, 'value' => 'acme']], $code, 'acme-processed', [$code],
            ],
            'list, camel case code' => [
                [['attributeCode' => $code, 'value' => 'acme']], $code, 'acme-processed', [$code],
            ],
            'keyed by code, scalar value' => [
                [$code => 'acme'], $code, 'acme-processed', [$code],
            ],
            'keyed by code, attribute array' => [
                [$code => ['attribute_code' => $code, 'value' => 'acme']], $code, 'acme-processed', [$code],
            ],
            'list, value supplied by the preprocessor' => [
                [['attribute_code' => $code]], $code, 'filled-processed', [$code],
            ],
            'keyed by code, value supplied by the preprocessor' => [
                [$code => ['attribute_code' => $code]], $code, 'filled-processed', [$code],
            ],
            'attribute without a preprocessor' => [
                [['attribute_code' => 'color', 'value' => 'red']], 'color', 'red', [],
            ],
        ];
    }

    /**
     * @param array $customAttributes
     * @param string $expectedMessage
     */
    #[DataProvider('invalidCustomAttributesDataProvider')]
    public function testInvalidCustomAttributesAreRejected(array $customAttributes, string $expectedMessage): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->convert($customAttributes);
    }

    /**
     * @return array
     */
    public static function invalidCustomAttributesDataProvider(): array
    {
        return [
            'empty attribute' => [
                [[]], 'An empty custom attribute is specified.',
            ],
            'missing code' => [
                [['value' => 'acme']], 'A custom attribute is specified with a missing attribute code.',
            ],
            'missing value without a preprocessor' => [
                [['attribute_code' => 'color']], 'The "color" attribute code doesn\'t have a value set.',
            ],
        ];
    }

    /**
     * Run the custom attribute conversion of the service input processor
     *
     * @param array $customAttributes
     * @return AttributeValue[]
     */
    private function convert(array $customAttributes): array
    {
        $method = new ReflectionMethod($this->serviceInputProcessor, 'convertCustomAttributeValue');

        return $method->invoke($this->serviceInputProcessor, $customAttributes, 'Some\DataObject');
    }
}
