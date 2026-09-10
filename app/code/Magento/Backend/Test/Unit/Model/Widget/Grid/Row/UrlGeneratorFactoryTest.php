<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Widget\Grid\Row;

use Magento\Backend\Model\Widget\Grid\Row\GeneratorInterface;
use Magento\Backend\Model\Widget\Grid\Row\UrlGenerator;
use Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlGeneratorFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface&MockObject
     */
    private $objectManager;

    /**
     * @var UrlGeneratorFactory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->factory = new UrlGeneratorFactory($this->objectManager);
    }

    /**
     * A class that implements GeneratorInterface is instantiated and returned.
     */
    public function testCreatesGeneratorImplementingInterface(): void
    {
        $arguments = ['foo' => 'bar'];
        $generator = $this->createStub(GeneratorInterface::class);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(UrlGenerator::class, $arguments)
            ->willReturn($generator);

        $this->assertSame(
            $generator,
            $this->factory->createUrlGenerator(UrlGenerator::class, $arguments)
        );
    }

    /**
     * A class that does not implement GeneratorInterface is rejected by validating the
     * class name, so the object manager is never asked to instantiate it.
     */
    public function testRejectsClassNotImplementingInterfaceWithoutInstantiating(): void
    {
        $this->objectManager->expects($this->never())->method('create');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Passed wrong parameters');

        $this->factory->createUrlGenerator(\stdClass::class, ['foo' => 'bar']);
    }

    /**
     * An unknown class name is rejected without reaching object instantiation.
     */
    public function testRejectsUnknownClassWithoutInstantiating(): void
    {
        $this->objectManager->expects($this->never())->method('create');

        $this->expectException(\InvalidArgumentException::class);

        $this->factory->createUrlGenerator('Nonexistent\\Row\\UrlGenerator', []);
    }
}
