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
use Magento\NewRelicReporting\Model\Orders;
use Magento\NewRelicReporting\Model\ResourceModel\Orders as OrdersResource;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test for Orders model
 *
 * @covers \Magento\NewRelicReporting\Model\Orders
 */
class OrdersTest extends TestCase
{
    /**
     * Create Orders instance with minimal required dependencies
     * @return Orders
     * @throws Exception|LocalizedException
     */
    private function createOrders(): Orders
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $resource = $this->createMock(OrdersResource::class);

        return new Orders($context, $registry, $resource);
    }

    /**
     * Test that Users extends AbstractModel
     *
     * @return void
     * @throws Exception | LocalizedException
     */
    public function testItExtendsAbstractModel(): void
    {
        $orders = $this->createOrders();
        $this->assertInstanceOf(AbstractModel::class, $orders);
    }

    /**
     * Test that Orders initializes the correct resource model
     *
     * @return void
     * @throws Exception | LocalizedException
     */
    public function testItInitializesResourceModel(): void
    {
        $orders = $this->createOrders();

        $reflection = new ReflectionClass($orders);
        $resourceNameProperty = $reflection->getProperty('_resourceName');

        $this->assertEquals(
            OrdersResource::class,
            $resourceNameProperty->getValue($orders)
        );
    }
}
