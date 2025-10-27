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
use Magento\NewRelicReporting\Model\System;
use Magento\NewRelicReporting\Model\ResourceModel\System as SystemResource;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test for System model
 *
 * @covers \Magento\NewRelicReporting\Model\System
 */
class SystemTest extends TestCase
{
    /**
     * Create System instance with minimal required dependencies
     * @return System
     * @throws Exception|LocalizedException
     */
    private function createSystem(): System
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $resource = $this->createMock(SystemResource::class);

        return new System($context, $registry, $resource);
    }

    /**
     * Test that Users extends AbstractModel
     *
     * @return void
     * @throws Exception | LocalizedException
     */
    public function testItExtendsAbstractModel(): void
    {
        $system = $this->createSystem();
        $this->assertInstanceOf(AbstractModel::class, $system);
    }

    /**
     * Test that System initializes the correct resource model
     *
     * @return void
     * @throws Exception | LocalizedException
     */
    public function testItInitializesResourceModel(): void
    {
        $system = $this->createSystem();

        $reflection = new ReflectionClass($system);
        $resourceNameProperty = $reflection->getProperty('_resourceName');
        $resourceNameProperty->setAccessible(true);

        $this->assertEquals(
            SystemResource::class,
            $resourceNameProperty->getValue($system)
        );
    }
}
