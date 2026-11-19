<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory;

use Magento\Framework\ObjectManager\Config\Config;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\ObjectManager\Factory\Compiled;
use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\Factory\Dynamic\Production;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional\Interceptor;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireRedeclared;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Preserve constructor inheritance, legacy definitions, and runtime fallbacks.
 */
class AutowireCompatibilityTest extends TestCase
{
    /**
     * Interceptors recover the parent's attribute without copying it.
     */
    #[DataProvider('factoryProvider')]
    public function testInterceptorInjectsOptionalDependency(string $factoryClass): void
    {
        $config = new Config();
        $factory = new $factoryClass($config);
        $objectManager = new ObjectManager($factory, $config);
        $factory->setObjectManager($objectManager);

        $instance = $factory->create(Interceptor::class);

        self::assertSame($objectManager->get(\stdClass::class), $instance->dependency);
        self::assertNull($instance->unattributed);
        self::assertSame([$instance->dependency, null], $instance->forwardedArguments);
    }

    /**
     * Cover both runtime factories for compatibility behavior.
     *
     * @return array
     */
    public static function factoryProvider(): array
    {
        return [[Developer::class], [Production::class]];
    }

    /**
     * A handwritten constructor does not inherit injection from its parent.
     */
    public function testRedeclaredConstructorDoesNotInheritAttribute(): void
    {
        $factory = new Developer(new Config());

        $instance = $factory->create(AutowireRedeclared::class);

        self::assertNull($instance->dependency);
        self::assertSame([null, null], $instance->forwardedArguments);
    }

    /**
     * Definitions cached without the new flag retain the previous behavior.
     */
    #[DataProvider('factoryProvider')]
    public function testAcceptsLegacyTuple(string $factoryClass): void
    {
        $definitions = $this->createMock(DefinitionInterface::class);
        $definitions->method('getParameters')->willReturn([
            ['dependency', \stdClass::class, false, null, false],
            ['unattributed', \stdClass::class, false, null, false],
        ]);
        $factory = new $factoryClass(new Config(), null, $definitions);

        self::assertNull($factory->create(AutowireOptional::class)->dependency);
    }

    /**
     * A missing compiled entry resolves the attributed dependency through AbstractFactory.
     */
    public function testCompiledFactoryRuntimeFallback(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('getArguments')->willReturnMap([
            [AutowireOptional::class, null],
            [\stdClass::class, []],
        ]);
        $config->method('getInstanceType')->willReturnMap([
            [AutowireOptional::class, AutowireOptional::class],
            [\stdClass::class, \stdClass::class],
        ]);
        $config->method('getPreference')->willReturnCallback(static fn (string $type): string => $type);
        $config->method('isShared')->willReturnMap([[\stdClass::class, true]]);
        $factory = new Compiled($config);
        $objectManager = new ObjectManager($factory, $config);
        $factory->setObjectManager($objectManager);

        $instance = $factory->create(AutowireOptional::class);

        self::assertSame($objectManager->get(\stdClass::class), $instance->dependency);
        self::assertNull($instance->unattributed);
    }
}
