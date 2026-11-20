<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use Laminas\Code\Reflection\ClassReflection;
use Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypes;
use Magento\Framework\Reflection\TypeProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TypeProcessorNativeTypesTest extends TestCase
{
    /**
     * @var TypeProcessor
     */
    private TypeProcessor $typeProcessor;

    protected function setUp(): void
    {
        $this->typeProcessor = new TypeProcessor();
    }

    #[DataProvider('compatibleReturnProvider')]
    public function testCompatibleReturnMetadata(string $method, array $expected): void
    {
        $reflection = new ClassReflection(NativeTypes\CompatibilityInterface::class);
        self::assertSame($expected, $this->typeProcessor->getGetterReturnType($reflection->getMethod($method)));
    }

    public static function compatibleReturnProvider(): array
    {
        return [
            'PHPDoc only' => ['documented', [
                'type' => 'string', 'isRequired' => true, 'description' => 'Label.', 'parameterCount' => 1,
            ]],
            'matching native' => ['matching', [
                'type' => 'string', 'isRequired' => true, 'description' => 'Label.', 'parameterCount' => 1,
            ]],
            'nullable PHPDoc' => ['nullable', [
                'type' => '\stdClass', 'isRequired' => false, 'description' => 'Child.', 'parameterCount' => 0,
            ]],
            'disagreeing native' => ['disagreeing', [
                'type' => 'string', 'isRequired' => true, 'description' => 'Label.', 'parameterCount' => 1,
            ]],
            'empty return tag' => ['emptyReturn', [
                'type' => null, 'isRequired' => true, 'description' => null, 'parameterCount' => 0,
            ]],
        ];
    }

    #[DataProvider('compatibleParameterProvider')]
    public function testCompatibleParameterMetadata(string $method, array $types, array $descriptions): void
    {
        $reflection = new ClassReflection(NativeTypes\CompatibilityInterface::class);
        foreach ($reflection->getMethod($method)->getParameters() as $index => $parameter) {
            self::assertSame($types[$index], $this->typeProcessor->getParamType($parameter));
            self::assertSame($descriptions[$index], $this->typeProcessor->getParamDescription($parameter));
        }
    }

    public static function compatibleParameterProvider(): array
    {
        return [
            ['documented', ['int'], ['Identifier.']],
            ['matching', ['int'], ['Identifier.']],
            ['disagreeing', ['int'], ['Identifier.']],
            ['reordered', ['string[]', 'int[]'], ['Second.', 'First.']],
        ];
    }

    #[DataProvider('nativeReturnProvider')]
    public function testNativeReturnMetadata(string $class, string $method, array $expected): void
    {
        $reflection = new ClassReflection($class);
        self::assertSame($expected, $this->typeProcessor->getGetterReturnType($reflection->getMethod($method)));
    }

    public static function nativeReturnProvider(): array
    {
        $native = NativeTypes\NativeOnlyInterface::class;
        $walk = NativeTypes\WalkChild::class;
        return [
            [$native, 'getId', [
                'type' => 'int', 'isRequired' => true, 'description' => '', 'parameterCount' => 0,
            ]],
            [$native, 'getChild', [
                'type' => '\stdClass', 'isRequired' => true, 'description' => '', 'parameterCount' => 0,
            ]],
            [$native, 'getNullable', [
                'type' => '\stdClass', 'isRequired' => false, 'description' => '', 'parameterCount' => 0,
            ]],
            [$native, 'reset', [
                'type' => 'void', 'isRequired' => true, 'description' => '', 'parameterCount' => 0,
            ]],
            [$native, 'getSelf', [
                'type' => '\\' . $native, 'isRequired' => true, 'description' => '', 'parameterCount' => 0,
            ]],
            [$native, 'getStatic', [
                'type' => '\\' . $native, 'isRequired' => true, 'description' => '', 'parameterCount' => 0,
            ]],
            [$native, 'getWithParameter', [
                'type' => 'string', 'isRequired' => true, 'description' => '', 'parameterCount' => 1,
            ]],
            [$walk, 'getFromInterface', [
                'type' => 'string', 'isRequired' => true, 'description' => 'Contract.', 'parameterCount' => 0,
            ]],
            [$walk, 'getFromUndocumented', [
                'type' => 'int', 'isRequired' => true, 'description' => '', 'parameterCount' => 0,
            ]],
            [$walk, 'getFromParent', [
                'type' => 'int', 'isRequired' => true, 'description' => '', 'parameterCount' => 0,
            ]],
            [$walk, 'getDocumentedParent', [
                'type' => 'string', 'isRequired' => true, 'description' => 'Parent.', 'parameterCount' => 0,
            ]],
        ];
    }

    #[DataProvider('unknownReturnProvider')]
    public function testUnknownReturnMetadata(
        string $class,
        string $message,
        string $method = 'getUnknown'
    ): void {
        $reflection = new ClassReflection($class);
        try {
            $this->typeProcessor->getGetterReturnType($reflection->getMethod($method));
            self::fail('Expected an unknown return type exception.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame($message, $exception->getMessage());
        }
    }

    public static function unknownReturnProvider(): array
    {
        return [
            [NativeTypes\NativeOnlyInterface::class,
                'Return type of ' . NativeTypes\NativeOnlyInterface::class . '::getUnknown() is unknown. '
                . 'Declare a native return type or add a @return annotation.'],
            [NativeTypes\WalkChild::class,
                "Method's return type must be specified using @return annotation. See "
                . NativeTypes\WalkChild::class . '::getUnknown()'],
            [NativeTypes\WalkChild::class,
                "Method's return type must be specified using @return annotation. See "
                . NativeTypes\WalkChild::class . '::getUnknownParent()', 'getUnknownParent'],
        ];
    }

    #[DataProvider('invalidNativeParameterProvider')]
    public function testInvalidNativeParameter(string $method, string $message): void
    {
        $reflection = new ClassReflection(NativeTypes\ParametersInterface::class);
        $parameter = $reflection->getMethod($method)->getParameters()[0];
        try {
            $this->typeProcessor->getParamType($parameter);
            self::fail('Expected an invalid parameter type exception.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame($message, $exception->getMessage());
        }
    }

    public static function invalidNativeParameterProvider(): array
    {
        $class = NativeTypes\ParametersInterface::class;
        return [
            ['union', 'Parameter "$value" of ' . $class . '::union() declares type "string|int", '
                . 'which the Web API cannot map. Use a single type.'],
            ['arrayWithoutTag', 'Parameter "$items" of ' . $class . '::arrayWithoutTag() declares "array" '
                . 'without an item type. Add a @param annotation such as Foo[] $items.'],
        ];
    }

    public function testMissingPositionalParameterUsesNamedTag(): void
    {
        $reflection = new ClassReflection(NativeTypes\ParametersInterface::class);
        $parameter = $reflection->getMethod('save')->getParameters()[1];
        self::assertSame('Foo[]', $this->typeProcessor->getParamType($parameter));
        self::assertSame('Items text', $this->typeProcessor->getParamDescription($parameter));
        self::assertSame('', $this->typeProcessor->getParamDescription(
            $reflection->getMethod('save')->getParameters()[0]
        ));
    }

    public function testMissingParameterDescriptions(): void
    {
        $reflection = new ClassReflection(NativeTypes\ParametersInterface::class);
        self::assertSame('', $this->typeProcessor->getParamDescription(
            $reflection->getMethod('arrayWithoutTag')->getParameters()[0]
        ));
        self::assertSame('', $this->typeProcessor->getParamDescription(
            $reflection->getMethod('incomplete')->getParameters()[1]
        ));
    }

    public function testUndocumentedMethodHasNoExceptions(): void
    {
        $reflection = new ClassReflection(NativeTypes\NativeOnlyInterface::class);
        self::assertSame([], $this->typeProcessor->getExceptions($reflection->getMethod('getId')));
    }

    public function testNativeGetterWithoutDocblockHasEmptyFieldDescription(): void
    {
        require_once __DIR__ . '/Fixture/NativeTypes/RegisteredDataInterface.php';

        $nameFinder = new \ReflectionProperty(TypeProcessor::class, 'nameFinder');
        $nameFinder->setValue($this->typeProcessor, new \Magento\Framework\Reflection\NameFinder());
        $type = $this->typeProcessor->register(\Magento\NativeTypes\Api\Data\RegisteredDataInterface::class);
        self::assertSame([
            'documentation' => '',
            'parameters' => [
                'id' => ['type' => 'int', 'required' => true, 'documentation' => ''],
            ],
        ], $this->typeProcessor->getTypeData($type));
    }

    public function testArrayParameterWithoutPositionalOrNamedTag(): void
    {
        $class = NativeTypes\ParametersInterface::class;
        $reflection = new ClassReflection($class);
        try {
            $this->typeProcessor->getParamType($reflection->getMethod('arrayMissingTag')->getParameters()[1]);
            self::fail('Expected an array item type exception.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(
                'Parameter "$items" of ' . $class . '::arrayMissingTag() declares "array" without an item type. '
                . 'Add a @param annotation such as Foo[] $items.',
                $exception->getMessage()
            );
        }
    }

    public function testUntypedUndocumentedParameterKeepsEmptyType(): void
    {
        $reflection = new ClassReflection(NativeTypes\ParametersInterface::class);
        self::assertSame('', $this->typeProcessor->getParamType(
            $reflection->getMethod('untyped')->getParameters()[0]
        ));
    }

    public function testPartialArrayTagsUseNamesBeforePositions(): void
    {
        $reflection = new ClassReflection(NativeTypes\ParametersInterface::class);
        $parameters = $reflection->getMethod('partialArrays')->getParameters();
        self::assertSame('Foo[]', $this->typeProcessor->getParamType($parameters[1]));
        self::assertSame('Bar[]', $this->typeProcessor->getParamType($parameters[2]));
        self::assertSame('', $this->typeProcessor->getParamDescription($parameters[0]));
        self::assertSame('First items.', $this->typeProcessor->getParamDescription($parameters[1]));
        self::assertSame('Second items.', $this->typeProcessor->getParamDescription($parameters[2]));
    }

    public function testPartialUnnamedTagKeepsPositionalType(): void
    {
        $reflection = new ClassReflection(NativeTypes\ParametersInterface::class);
        $parameter = $reflection->getMethod('unnamedTag')->getParameters()[0];
        self::assertSame('Foo[]', $this->typeProcessor->getParamType($parameter));
        self::assertNull($this->typeProcessor->getParamDescription($parameter));
    }

    public function testPartialUnknownVariableTagKeepsPositionalMetadata(): void
    {
        $reflection = new ClassReflection(NativeTypes\ParametersInterface::class);
        $parameter = $reflection->getMethod('unknownTag')->getParameters()[0];
        self::assertSame('Foo[]', $this->typeProcessor->getParamType($parameter));
        self::assertSame('Unknown items.', $this->typeProcessor->getParamDescription($parameter));
    }

    public function testPartialTypeFallbackCanUseAnotherParametersTag(): void
    {
        $reflection = new ClassReflection(NativeTypes\ParametersInterface::class);
        $parameters = $reflection->getMethod('otherParameterTag')->getParameters();
        self::assertSame('Foo[]', $this->typeProcessor->getParamType($parameters[0]));
        self::assertSame('', $this->typeProcessor->getParamDescription($parameters[0]));
        self::assertSame('Flag text.', $this->typeProcessor->getParamDescription($parameters[1]));
    }
}
