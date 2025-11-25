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
use Magento\NewRelicReporting\Model\Counts;
use Magento\NewRelicReporting\Model\ResourceModel\Counts as CountsResource;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test for Counts model
 *
 * @covers \Magento\NewRelicReporting\Model\Counts
 */
class CountsTest extends TestCase
{
    /**
     * Create Counts instance with minimal required dependencies
     *
     * @return Counts
     * @throws LocalizedException|Exception
     */
    private function createCounts(): Counts
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $resource = $this->createMock(CountsResource::class);

        return new Counts($context, $registry, $resource);
    }

    /**
     * @return void
     * @throws Exception|LocalizedException
     */
    public function testItExtendsAbstractModel()
    {
        $counts = $this->createCounts();
        $this->assertInstanceOf(AbstractModel::class, $counts);
    }

    /**
     *  Test that Counts initializes the correct resource model
     * @return void
     * @throws Exception|LocalizedException
     */
    public function testItInitializesResourceModel()
    {
        $counts = $this->createCounts();

        $reflection = new ReflectionClass($counts);
        $resourceNameProperty = $reflection->getProperty('_resourceName');
        $resourceNameProperty->setAccessible(true);
        $this->assertEquals(
            CountsResource::class,
            $resourceNameProperty->getValue($counts)
        );
    }
}
