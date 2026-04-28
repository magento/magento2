<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Tests ObjectManagerProvider
 */
class ObjectManagerProviderTest extends TestCase
{
    /**
     * @var ObjectManagerProvider
     */
    private $object;

    /**
     * @var ServiceLocatorInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $locator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->locator = $this->createMock(ServiceLocatorInterface::class);
        $this->object = new ObjectManagerProvider($this->locator, new Bootstrap());
        $this->locator->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [InitParamListener::BOOTSTRAP_PARAM, []],
                    [Application::class, $this->createMock(Application::class)],
                ]
            );
    }

    /**
     * Tests the same instance of ObjectManagerInterface should be provided by the ObjectManagerProvider
     */
    public function testGet()
    {
        $objectManager = $this->object->get();
        $this->assertInstanceOf(ObjectManagerInterface::class, $objectManager);
        $this->assertSame($objectManager, $this->object->get());
    }
}
