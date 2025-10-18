<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\NewRelicReporting\Model\Module;
use Magento\NewRelicReporting\Model\ResourceModel\Module as ModuleResource;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test for Module model
 *
 * @covers \Magento\NewRelicReporting\Model\Module
 */
class ModuleTest extends TestCase
{
    /**
     * Create Module instance with minimal required dependencies
     *
     * @return Module
     * @throws LocalizedException|Exception
     */
    private function createModule(): Module
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $resource = $this->createMock(ModuleResource::class);

        return new Module($context, $registry, $resource);
    }

    /**
     * @return void
     * @throws Exception|LocalizedException
     */
    public function testItExtendsAbstractModel()
    {
        $module = $this->createModule();
        $this->assertInstanceOf(AbstractModel::class, $module);
    }

    /**
     *  Test that Module initializes the correct resource model
     * @return void
     * @throws Exception|LocalizedException
     */
    public function testItInitializesResourceModel()
    {
        $module = $this->createModule();

        $reflection = new ReflectionClass($module);
        $resourceNameProperty = $reflection->getProperty('_resourceName');
        $resourceNameProperty->setAccessible(true);
        $this->assertEquals(
            ModuleResource::class,
            $resourceNameProperty->getValue($module)
        );
    }
}
