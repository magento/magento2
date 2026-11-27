<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Reflection\NativeTypeResolver;
use Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypeResolver\ChildInterface;
use Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypeResolver\ParameterTypes;
use Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypeResolver\ReturnInterface;
use Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypeResolver\ReturnTypes;
use Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypeResolver\Status;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verify native return metadata and composite parameter diagnostics.
 */
class NativeTypeResolverTest extends TestCase
{
    public static function acceptedReturnProvider(): array
    {
        return [
            ...self::acceptedScalarReturnProvider(),
            ...self::acceptedClassReturnProvider(),
            ...self::acceptedSpecialReturnProvider(),
        ];
    }

    #[DataProvider('acceptedReturnProvider')]
    public function testResolveAcceptedReturn(string $class, string $method, ?array $expected): void
    {
        self::assertSame(
            $expected,
            (new NativeTypeResolver())->resolveReturn(new \ReflectionMethod($class, $method))
        );
    }

    public static function acceptedScalarReturnProvider(): array
    {
        return [
            'integer' => [ReturnTypes::class, 'integer', [
                'type' => 'int',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'string' => [ReturnTypes::class, 'string', [
                'type' => 'string',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'boolean' => [ReturnTypes::class, 'boolean', [
                'type' => 'bool',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'float' => [ReturnTypes::class, 'float', [
                'type' => 'float',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableInteger' => [ReturnTypes::class, 'nullableInteger', [
                'type' => 'int',
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableString' => [ReturnTypes::class, 'nullableString', [
                'type' => 'string',
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableBoolean' => [ReturnTypes::class, 'nullableBoolean', [
                'type' => 'bool',
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableFloat' => [ReturnTypes::class, 'nullableFloat', [
                'type' => 'float',
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullUnion' => [ReturnTypes::class, 'nullUnion', [
                'type' => 'int',
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
        ];
    }

    public static function acceptedClassReturnProvider(): array
    {
        return [
            'className' => [ReturnTypes::class, 'className', [
                'type' => '\\DateTime',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableClass' => [ReturnTypes::class, 'nullableClass', [
                'type' => '\\DateTime',
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'interfaceName' => [ReturnTypes::class, 'interfaceName', [
                'type' => '\\DateTimeInterface',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableInterface' => [ReturnTypes::class, 'nullableInterface', [
                'type' => '\\DateTimeInterface',
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'enumName' => [ReturnTypes::class, 'enumName', [
                'type' => '\\' . Status::class,
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableEnum' => [ReturnTypes::class, 'nullableEnum', [
                'type' => '\\' . Status::class,
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
        ];
    }

    public static function acceptedSpecialReturnProvider(): array
    {
        return [
            'selfName' => [ReturnTypes::class, 'selfName', [
                'type' => '\\' . ReturnTypes::class,
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableSelf' => [ReturnTypes::class, 'nullableSelf', [
                'type' => '\\' . ReturnTypes::class,
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'staticName' => [ReturnTypes::class, 'staticName', [
                'type' => '\\' . ReturnTypes::class,
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableStatic' => [ReturnTypes::class, 'nullableStatic', [
                'type' => '\\' . ReturnTypes::class,
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'parentName' => [ReturnTypes::class, 'parentName', [
                'type' => '\\stdClass',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'nullableParent' => [ReturnTypes::class, 'nullableParent', [
                'type' => '\\stdClass',
                'isRequired' => false,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'mixedType' => [ReturnTypes::class, 'mixedType', [
                'type' => 'mixed',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'voidType' => [ReturnTypes::class, 'voidType', [
                'type' => 'void',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'required parameter count' => [ReturnTypes::class, 'withParameters', [
                'type' => 'int',
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 2,
            ]],
            'inherited static uses declaring interface' => [ChildInterface::class, 'staticName', [
                'type' => '\\' . ReturnInterface::class,
                'isRequired' => true,
                'description' => '',
                'parameterCount' => 0,
            ]],
            'no return type' => [ReturnTypes::class, 'untyped', null],
        ];
    }

    #[DataProvider('notLoadedReturnProvider')]
    public function testResolveReturnWithoutAutoloading(string $method, bool $isRequired): void
    {
        $className = __NAMESPACE__ . '\Fixture\NativeTypeResolver\NotLoadedReturn';
        self::assertFalse(class_exists($className, false));
        $reflection = new \ReflectionMethod(ReturnTypes::class, $method);
        $resolver = new NativeTypeResolver();
        $autoloadedClasses = [];
        $autoloader = static function (string $name) use (&$autoloadedClasses): void {
            $autoloadedClasses[] = $name;
        };

        spl_autoload_register($autoloader, true, true);
        try {
            $result = $resolver->resolveReturn($reflection);
        } finally {
            spl_autoload_unregister($autoloader);
        }

        self::assertSame([
            'type' => '\\' . $className,
            'isRequired' => $isRequired,
            'description' => '',
            'parameterCount' => 0,
        ], $result);
        self::assertFalse(class_exists($className, false));
        self::assertNotContains($className, $autoloadedClasses);
    }

    public static function notLoadedReturnProvider(): array
    {
        return [
            'unloaded class' => ['notLoadedClass', true],
            'nullable unloaded class' => ['nullableNotLoadedClass', false],
        ];
    }

    #[DataProvider('rejectedReturnProvider')]
    public function testResolveRejectedReturn(string $method, string $type, bool $missingItemType): void
    {
        $message = $missingItemType
            ? 'Method %1$s::%2$s() declares return type "%3$s" without an item type. '
                . 'Add a @return annotation such as Foo[].'
            : 'Method %1$s::%2$s() declares return type "%3$s", which the Web API cannot map. '
                . 'Use a single supported type or add a @return annotation.';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^' . preg_quote(sprintf(
            $message,
            ReturnTypes::class,
            $method,
            $type
        ), '/') . '$/D');

        (new NativeTypeResolver())->resolveReturn(new \ReflectionMethod(ReturnTypes::class, $method));
    }

    public static function rejectedReturnProvider(): array
    {
        return [
            'arrayType' => ['arrayType', 'array', true],
            'nullableArray' => ['nullableArray', '?array', true],
            'iterableType' => ['iterableType', 'iterable', true],
            'nullableIterable' => ['nullableIterable', '?iterable', true],
            'unionType' => ['unionType', 'string|int', false],
            'nullableUnion' => ['nullableUnion', 'string|int|null', false],
            'intersectionType' => ['intersectionType', 'Countable&Stringable', false],
            'dnfType' => ['dnfType', '(Countable&Stringable)|stdClass', false],
            'neverType' => ['neverType', 'never', false],
            'objectType' => ['objectType', 'object', false],
            'callableType' => ['callableType', 'callable', false],
        ];
    }

    #[DataProvider('unsupportedNamedReturnProvider')]
    public function testRejectUnsupportedNamedReturn(string $name): void
    {
        $type = $this->createStub(\ReflectionNamedType::class);
        $type->method('getName')->willReturn($name);
        $type->method('isBuiltin')->willReturn(true);
        $type->method('__toString')->willReturn($name);
        $method = $this->createStub(\ReflectionMethod::class);
        $method->method('getReturnType')->willReturn($type);
        $method->method('getDeclaringClass')->willReturn(new \ReflectionClass(ReturnTypes::class));
        $method->method('getName')->willReturn($name . 'Type');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^' . preg_quote(sprintf(
            'Method %1$s::%2$s() declares return type "%3$s", which the Web API cannot map. '
                . 'Use a single supported type or add a @return annotation.',
            ReturnTypes::class,
            $name . 'Type',
            $name
        ), '/') . '$/D');

        (new NativeTypeResolver())->resolveReturn($method);
    }

    public static function unsupportedNamedReturnProvider(): array
    {
        return [
            'nullType' => ['null'],
            'falseType' => ['false'],
            'trueType' => ['true'],
        ];
    }

    public function testRejectParentWithoutParentClass(): void
    {
        $type = $this->createStub(\ReflectionNamedType::class);
        $type->method('getName')->willReturn('parent');
        $type->method('__toString')->willReturn('parent');
        $method = $this->createStub(\ReflectionMethod::class);
        $method->method('getReturnType')->willReturn($type);
        $method->method('getDeclaringClass')->willReturn(new \ReflectionClass(ReturnInterface::class));
        $method->method('getName')->willReturn('parentName');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^' . preg_quote(sprintf(
            'Method %s::parentName() declares return type "parent", which the Web API cannot map. '
                . 'Use a single supported type or add a @return annotation.',
            ReturnInterface::class
        ), '/') . '$/D');

        (new NativeTypeResolver())->resolveReturn($method);
    }

    #[DataProvider('namedParameterProvider')]
    public function testAcceptNamedOrUntypedParameter(string $method): void
    {
        $parameter = (new \ReflectionMethod(ParameterTypes::class, $method))->getParameters()[0];

        $this->expectNotToPerformAssertions();

        (new NativeTypeResolver())->assertNamedParameter($parameter);
    }

    public static function namedParameterProvider(): array
    {
        return [
            'untyped' => ['untypedParameter'],
            'builtin' => ['builtinParameter'],
            'class' => ['classParameter'],
            'nullable' => ['nullableParameter'],
        ];
    }

    #[DataProvider('compositeParameterProvider')]
    public function testRejectCompositeParameter(string $method, string $type): void
    {
        $parameter = (new \ReflectionMethod(ParameterTypes::class, $method))->getParameters()[0];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^' . preg_quote(sprintf(
            'Parameter "$%4$s" of %1$s::%2$s() declares type "%3$s", which the Web API cannot map. '
                . 'Use a single type.',
            ParameterTypes::class,
            $method,
            $type,
            'value'
        ), '/') . '$/D');

        (new NativeTypeResolver())->assertNamedParameter($parameter);
    }

    public static function compositeParameterProvider(): array
    {
        return [
            'union' => ['unionParameter', 'string|int'],
            'intersection' => ['intersectionParameter', 'Countable&Stringable'],
            'DNF' => ['dnfParameter', '(Countable&Stringable)|stdClass'],
        ];
    }
}
