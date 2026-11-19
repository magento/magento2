<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Compiler;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional\Interceptor;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireRedeclared;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Setup\Module\Di\Compiler\ArgumentsResolver;
use Magento\Setup\Module\Di\Compiler\Config\Chain\InterceptorSubstitution;
use Magento\Setup\Module\Di\Compiler\ConstructorArgument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Preserve constructor inheritance and compatibility during compilation.
 */
class AutowireCompatibilityTest extends TestCase
{
    /**
     * Interceptors recover parent attributes while handwritten constructors opt out.
     */
    #[DataProvider('constructorProvider')]
    public function testCompilesConstructor(string $className, bool $autowire): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getArguments')->willReturn([]);
        $config->method('isShared')->willReturnMap([[\stdClass::class, true]]);
        $constructor = (new ClassReaderDecorator(new ClassReader()))->getConstructor($className);

        self::assertFalse($constructor[0]->isRequired());
        self::assertSame($autowire, $constructor[0]->isAutowire());
        self::assertSame(
            [
                'dependency' => $autowire ? ['_i_' => \stdClass::class] : ['_vn_' => true],
                'unattributed' => ['_vn_' => true],
            ],
            (new ArgumentsResolver($config))->getResolvedConstructorArguments($className, $constructor)
        );
    }

    /**
     * Cover constructors whose parameter lists do not carry attributes.
     *
     * @return array
     */
    public static function constructorProvider(): array
    {
        return [
            'handwritten' => [AutowireRedeclared::class, false],
            'interceptor' => [Interceptor::class, true],
        ];
    }

    /**
     * Five-element definitions do not opt into injection.
     */
    public function testAcceptsLegacyTuple(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getArguments')->willReturn([]);
        $config->expects($this->never())->method('isShared');
        $argument = new ConstructorArgument(['dependency', \stdClass::class, false, null, false]);

        self::assertFalse($argument->isAutowire());
        self::assertSame(
            ['dependency' => ['_vn_' => true]],
            (new ArgumentsResolver($config))->getResolvedConstructorArguments(AutowireOptional::class, [$argument])
        );
    }

    /**
     * Null configuration retains the existing compiler behavior without the attribute.
     */
    public function testUnattributedNullConfigurationIsUnchanged(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getArguments')->willReturn(['optional' => null, 'required' => null]);
        $config->method('isShared')->willReturnMap([[\stdClass::class, true]]);

        self::assertSame(
            ['optional' => ['_v_' => 'default'], 'required' => ['_i_' => \stdClass::class]],
            (new ArgumentsResolver($config))->getResolvedConstructorArguments(AutowireOptional::class, [
                new ConstructorArgument(['optional', null, false, 'default', false]),
                new ConstructorArgument(['required', \stdClass::class, true, null, false]),
            ])
        );
    }

    /**
     * Substitution preserves the injection metadata when routing to the Interceptor.
     */
    public function testInterceptorSubstitutionPreservesInjection(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getArguments')->willReturn([]);
        $config->method('isShared')->willReturnMap([[\stdClass::class, true]]);
        $reader = new ClassReaderDecorator(new ClassReader());
        $resolver = new ArgumentsResolver($config);
        $compiled = [];
        foreach ([AutowireOptional::class, Interceptor::class] as $className) {
            $compiled[$className] = $resolver->getResolvedConstructorArguments(
                $className,
                $reader->getConstructor($className)
            );
        }

        $result = (new InterceptorSubstitution())->modify([
            'arguments' => $compiled,
            'preferences' => [],
            'instanceTypes' => [],
        ]);

        self::assertSame(
            [Interceptor::class => ['dependency' => ['_i_' => \stdClass::class], 'unattributed' => ['_vn_' => true]]],
            $result['arguments']
        );
        self::assertSame(Interceptor::class, $result['preferences'][AutowireOptional::class]);
    }
}
