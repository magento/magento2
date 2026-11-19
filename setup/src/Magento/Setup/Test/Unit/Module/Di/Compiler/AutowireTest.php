<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Compiler;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireIgnored;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireInterface;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Setup\Module\Di\Compiler\ArgumentsResolver;
use Magento\Setup\Module\Di\Compiler\ConstructorArgument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Compile optional injection from reflected classes through the real decorator.
 */
class AutowireTest extends TestCase
{
    /**
     * Preserve requiredness while compiling attributed optional arguments as instances.
     */
    #[DataProvider('constructorProvider')]
    public function testCompilesConstructor(string $className, bool $autowire, bool $shared): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getArguments')->with($className)->willReturn([]);
        $config->method('isShared')->willReturnMap([[\stdClass::class, $shared]]);
        $constructor = (new ClassReaderDecorator(new ClassReader()))->getConstructor($className);

        self::assertFalse($constructor[0]->isRequired());
        self::assertSame($autowire, $constructor[0]->isAutowire());
        self::assertSame(
            [
                'dependency' => $autowire ? [$shared ? '_i_' : '_ins_' => \stdClass::class] : ['_vn_' => true],
                'unattributed' => ['_vn_' => true],
            ],
            (new ArgumentsResolver($config))->getResolvedConstructorArguments($className, $constructor)
        );
    }

    /**
     * Cover shared and nonshared instances.
     *
     * @return array
     */
    public static function constructorProvider(): array
    {
        return [
            'plain shared' => [AutowireOptional::class, true, true],
            'plain nonshared' => [AutowireOptional::class, true, false],
        ];
    }

    /**
     * Interpreted di.xml arguments override the attribute, including null.
     */
    #[DataProvider('configuredArgumentProvider')]
    public function testConfiguredArgumentWins(array $arguments, array $expected): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getArguments')->with(AutowireOptional::class)->willReturn($arguments);
        $config->method('isShared')->willReturnMap([
            [\stdClass::class, true],
            ['ConfiguredDependency', false],
        ]);
        $constructor = (new ClassReaderDecorator(new ClassReader()))->getConstructor(AutowireOptional::class);

        self::assertSame(
            ['dependency' => $expected, 'unattributed' => ['_vn_' => true]],
            (new ArgumentsResolver($config))->getResolvedConstructorArguments(AutowireOptional::class, $constructor)
        );
    }

    /**
     * Supply instance and null overrides.
     *
     * @return array
     */
    public static function configuredArgumentProvider(): array
    {
        return [
            'instance' => [
                ['dependency' => ['instance' => 'ConfiguredDependency']],
                ['_ins_' => 'ConfiguredDependency'],
            ],
            'null' => [['dependency' => null], ['_vn_' => true]],
        ];
    }

    /**
     * Unsupported parameters compile to their defaults without resolving any instances.
     */
    public function testIgnoresUnsupportedParameters(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getArguments')->willReturn([]);
        $config->expects($this->never())->method('isShared');
        $constructor = (new ClassReaderDecorator(new ClassReader()))->getConstructor(AutowireIgnored::class);

        self::assertSame(
            [
                'scalar' => ['_v_' => 7],
                'array' => ['_v_' => []],
                'untyped' => ['_vn_' => true],
                'union' => ['_vn_' => true],
                'intersection' => ['_vn_' => true],
                'variadic' => ['_v_' => []],
            ],
            (new ArgumentsResolver($config))->getResolvedConstructorArguments(AutowireIgnored::class, $constructor)
        );
    }

    /**
     * Interface dependencies use the existing instance format for preference resolution.
     */
    public function testCompilesInterfaceDependency(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getArguments')->willReturn([]);
        $config->method('isShared')->willReturnMap([[\Countable::class, true]]);
        $constructor = (new ClassReaderDecorator(new ClassReader()))->getConstructor(AutowireInterface::class);

        self::assertSame(
            ['dependency' => ['_i_' => \Countable::class]],
            (new ArgumentsResolver($config))->getResolvedConstructorArguments(AutowireInterface::class, $constructor)
        );
    }

    /**
     * Object-valued configuration is ignored exactly as for a required dependency.
     */
    public function testObjectConfigurationMatchesRequiredDependency(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getArguments')->willReturn(['dependency' => new \stdClass()]);
        $config->method('isShared')->willReturnMap([[\stdClass::class, true]]);
        $resolver = new ArgumentsResolver($config);
        $constructor = (new ClassReaderDecorator(new ClassReader()))->getConstructor(AutowireOptional::class);
        $required = new ConstructorArgument(['dependency', \stdClass::class, true, null, false]);

        $expected = ['dependency' => ['_i_' => \stdClass::class]];
        self::assertSame(
            $expected,
            $resolver->getResolvedConstructorArguments(AutowireOptional::class, [$required])
        );
        self::assertSame(
            $expected,
            $resolver->getResolvedConstructorArguments(AutowireOptional::class, [$constructor[0]])
        );
    }
}
