<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\Test\Unit\Reader;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Framework\ObjectManager\Attribute\Autowire;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireIgnored;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireInterface;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional\Interceptor;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireRedeclared;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Read optional injection metadata without changing PHP requiredness.
 */
class AutowireTest extends TestCase
{
    /**
     * Only generated Interceptor constructors recover the parent's attribute.
     */
    #[DataProvider('constructorProvider')]
    public function testReadsAutowireFlag(string $className, bool $expected): void
    {
        $reader = new ClassReader();
        $constructor = $reader->getConstructor($className);

        $expectedParameter = ['dependency', \stdClass::class, false, null, false];
        if ($expected) {
            $expectedParameter[] = true;
        }
        self::assertSame($expectedParameter, $constructor[0]);
        self::assertSame(['unattributed', \stdClass::class, false, null, false], $constructor[1]);
        self::assertSame($constructor, $reader->getConstructor($className));
    }

    /**
     * Include constructors whose reflected signatures omit attributes.
     *
     * @return array
     */
    public static function constructorProvider(): array
    {
        return [
            'plain' => [AutowireOptional::class, true],
            'handwritten' => [AutowireRedeclared::class, false],
            'interceptor' => [Interceptor::class, true],
        ];
    }

    /**
     * Scalar, array, untyped, union, intersection and variadic parameters are ignored.
     */
    public function testIgnoresUnsupportedParameters(): void
    {
        $constructor = (new ClassReader())->getConstructor(AutowireIgnored::class);

        self::assertSame([5, 5, 5, 5, 5, 5], array_map('count', $constructor));
        self::assertSame(['variadic', \stdClass::class, false, [], true], $constructor[5]);
    }

    /**
     * Interfaces are eligible for injection too.
     */
    public function testReadsInterfaceType(): void
    {
        self::assertSame(
            [['dependency', \Countable::class, false, null, false, true]],
            (new ClassReader())->getConstructor(AutowireInterface::class)
        );
    }

    /**
     * Promoted properties can instantiate the attribute without changing its constructor meaning.
     */
    public function testPromotedParameterAttribute(): void
    {
        $reflection = new \ReflectionClass(AutowireOptional::class);
        $constructor = $reflection->getConstructor();
        self::assertNotNull($constructor);
        $parameter = $constructor->getParameters()[0];

        self::assertTrue($parameter->isPromoted());
        self::assertInstanceOf(
            Autowire::class,
            $reflection->getProperty('dependency')->getAttributes(Autowire::class)[0]->newInstance()
        );
        self::assertTrue((new ClassReader())->getConstructor(AutowireOptional::class)[0][5]);
    }

    /**
     * The public attribute supports promoted properties and has no configuration.
     */
    public function testAttributeDeclaration(): void
    {
        $reflection = new \ReflectionClass(Autowire::class);

        self::assertFalse($reflection->isFinal());
        self::assertNull($reflection->getConstructor());
        self::assertSame(
            \Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY,
            $reflection->getAttributes(\Attribute::class)[0]->newInstance()->flags
        );
    }
}
