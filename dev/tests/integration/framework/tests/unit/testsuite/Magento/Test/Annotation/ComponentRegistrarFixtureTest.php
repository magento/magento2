<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Annotation;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Annotation\ComponentRegistrarFixture;
use Magento\TestFramework\Fixture\Parser\ComponentsDir;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;

class ComponentRegistrarFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    private const LIBRARY_NAME = 'magento/library';
    private const MODULE_NAME = 'Magento_ModuleOne';
    private const THEME_NAME = 'frontend/Magento/theme';
    private const LANGUAGE_NAME = 'magento_language';

    protected function setUp(): void
    {
        /** @var ObjectManagerInterface|MockObject $objectManager */
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['get', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $sharedInstances = [
            ComponentsDir::class => $this->createConfiguredMock(ComponentsDir::class, ['parse' => []])
        ];
        $objectManager->method('get')
            ->willReturnCallback(
                function (string $type) use ($sharedInstances) {
                    return $sharedInstances[$type] ?? new $type();
                }
            );
        $objectManager->method('create')
            ->willReturnCallback(
                function (string $type, array $arguments = []) {
                    return new $type(...array_values($arguments));
                }
            );
        Bootstrap::setObjectManager($objectManager);
        $this->componentRegistrar = new ComponentRegistrar();
    }

    /**
     * @magentoComponentsDir components
     */
    public function testStartEndTest()
    {
        $this->assertFixturesNotRegistered();
        $object = new ComponentRegistrarFixture(__DIR__ . '/_files');
        $object->startTest($this);
        $this->assertFixturesRegistered();
        $object->endTest($this);
        $this->assertFixturesNotRegistered();
    }

    private function assertFixturesNotRegistered()
    {
        $this->assertEmpty($this->componentRegistrar->getPath(ComponentRegistrar::LIBRARY, self::LIBRARY_NAME));
        $this->assertEmpty($this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::MODULE_NAME));
        $this->assertEmpty($this->componentRegistrar->getPath(ComponentRegistrar::THEME, self::THEME_NAME));
        $this->assertEmpty($this->componentRegistrar->getPath(ComponentRegistrar::LANGUAGE, self::LANGUAGE_NAME));
    }

    private function assertFixturesRegistered()
    {
        $this->assertSame(
            __DIR__ . '/_files/components/b',
            $this->componentRegistrar->getPath(ComponentRegistrar::LIBRARY, self::LIBRARY_NAME)
        );
        $this->assertSame(
            __DIR__ . '/_files/components',
            $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::MODULE_NAME)
        );
        $this->assertSame(
            __DIR__ . '/_files/components/a/aa/aaa',
            $this->componentRegistrar->getPath(ComponentRegistrar::THEME, self::THEME_NAME)
        );
        $this->assertSame(
            __DIR__ . '/_files/components/a/aa',
            $this->componentRegistrar->getPath(ComponentRegistrar::LANGUAGE, self::LANGUAGE_NAME)
        );
    }
}
