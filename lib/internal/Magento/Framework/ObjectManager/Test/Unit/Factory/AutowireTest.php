<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory;

use Magento\Framework\ObjectManager\Config\Config;
use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\Factory\Dynamic\Production;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireCountable;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireIgnored;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireInterface;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Exercise optional dependency injection through the runtime factories.
 */
class AutowireTest extends TestCase
{
    /**
     * Resolve only the attributed dependency, while retaining unattributed defaults.
     */
    #[DataProvider('factoryProvider')]
    public function testInjectsOptionalDependency(string $factoryClass, string $instanceType): void
    {
        $config = new Config();
        $factory = new $factoryClass($config);
        $objectManager = new ObjectManager($factory, $config);
        $factory->setObjectManager($objectManager);

        $instance = $factory->create($instanceType);

        self::assertSame($objectManager->get(\stdClass::class), $instance->dependency);
        self::assertNull($instance->unattributed);
    }

    /**
     * Cover both runtime resolvers.
     *
     * @return array
     */
    public static function factoryProvider(): array
    {
        return [
            [Developer::class, AutowireOptional::class],
            [Production::class, AutowireOptional::class],
        ];
    }

    /**
     * Configuration, including explicit null, takes precedence over the attribute.
     */
    #[DataProvider('configuredArgumentProvider')]
    public function testConfiguredArgumentWins(string $factoryClass, array $arguments, string $expectedType): void
    {
        $config = new Config();
        $config->extend([
            AutowireOptional::class => ['arguments' => $arguments],
            'ConfiguredDependency' => ['type' => \stdClass::class],
        ]);
        $factory = new $factoryClass($config);
        $objectManager = new ObjectManager($factory, $config);
        $factory->setObjectManager($objectManager);

        $instance = $factory->create(AutowireOptional::class);

        self::assertSame(
            $expectedType === '' ? null : $objectManager->get($expectedType),
            $instance->dependency
        );
    }

    /**
     * Supply the interpreted values of di.xml arguments.
     *
     * @return array
     */
    public static function configuredArgumentProvider(): array
    {
        return [
            'developer instance' => [
                Developer::class,
                ['dependency' => ['instance' => 'ConfiguredDependency']],
                'ConfiguredDependency',
            ],
            'developer null' => [Developer::class, ['dependency' => null], ''],
            'production instance' => [
                Production::class,
                ['dependency' => ['instance' => 'ConfiguredDependency']],
                'ConfiguredDependency',
            ],
            'production null' => [Production::class, ['dependency' => null], ''],
        ];
    }

    /**
     * Unsupported types and variadics keep their defaults.
     */
    public function testIgnoresUnsupportedParameters(): void
    {
        $factory = new Developer(new Config());
        $instance = $factory->create(AutowireIgnored::class);

        self::assertSame(7, $instance->scalar);
        self::assertSame([], $instance->array);
        self::assertNull($instance->untyped);
        self::assertNull($instance->union);
        self::assertNull($instance->intersection);
        self::assertSame([], $instance->variadic);
    }

    /**
     * An optional interface follows the same preference as a required dependency.
     */
    public function testResolvesInterfacePreference(): void
    {
        $config = new Config();
        $config->extend(['preferences' => [\Countable::class => AutowireCountable::class]]);
        $factory = new Developer($config);
        $objectManager = new ObjectManager($factory, $config);
        $factory->setObjectManager($objectManager);

        self::assertSame(
            $objectManager->get(\Countable::class),
            $factory->create(AutowireInterface::class)->dependency
        );
    }

    /**
     * Nonshared optional dependencies are created for each consuming instance.
     */
    public function testResolvesNonsharedDependency(): void
    {
        $config = new Config();
        $config->extend([\stdClass::class => ['shared' => false]]);
        $factory = new Developer($config);
        $factory->setObjectManager(new ObjectManager($factory, $config));

        self::assertNotSame(
            $factory->create(AutowireOptional::class)->dependency,
            $factory->create(AutowireOptional::class)->dependency
        );
    }

    /**
     * A missing interface preference fails rather than silently using null.
     */
    public function testUnresolvableDependencyFails(): void
    {
        $config = new Config();
        $factory = new Developer($config);
        $factory->setObjectManager(new ObjectManager($factory, $config));

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot instantiate interface Countable');
        $factory->create(AutowireInterface::class);
    }
}
