<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Plugin\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Plugin\Model\OrderRepositoryPlugin;

/**
 * Unit test for OrderRepositoryPlugin
 */
class OrderRepositoryPluginTest extends TestCase
{
    /**
     * @var OrderRepositoryPlugin
     */
    private $plugin;

    /**
     * @var OrderRepository|MockObject
     */
    private $orderRepository;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var OrderAddressInterface|MockObject
     */
    private $billingAddress;

    /**
     * @var OrderItemInterface|MockObject
     */
    private $orderItem;

    protected function setUp(): void
    {
        $this->plugin = new OrderRepositoryPlugin();
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->order = $this->createMock(Order::class);
        $this->billingAddress = $this->createMock(OrderAddressInterface::class);
        $this->orderItem = $this->createMock(OrderItemInterface::class);
    }

    /**
     * Test successful validation with valid order
     */
    public function testBeforeSaveWithValidOrder(): void
    {
        // Setup billing address
        $this->billingAddress->method('getFirstname')->willReturn('John');
        $this->billingAddress->method('getLastname')->willReturn('Doe');
        $this->billingAddress->method('getStreet')->willReturn(['123 Main St']);
        $this->billingAddress->method('getCity')->willReturn('City');
        $this->billingAddress->method('getCountryId')->willReturn('US');

        // Setup order item
        $this->orderItem->method('getProductId')->willReturn(1);
        $this->orderItem->method('getSku')->willReturn('simple-product');
        $this->orderItem->method('getQtyOrdered')->willReturn(2);

        // Setup order
        $this->order->method('getBillingAddress')->willReturn($this->billingAddress);
        $this->order->method('getAllVisibleItems')->willReturn([$this->orderItem]);

        $result = $this->plugin->beforeSave($this->orderRepository, $this->order);

        $this->assertEquals([$this->order], $result);
    }

    /**
     * Test validation fails with missing billing address
     */
    public function testBeforeSaveWithMissingBillingAddress(): void
    {
        $this->order->method('getBillingAddress')->willReturn(null);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please provide billing address for the order.');

        $this->plugin->beforeSave($this->orderRepository, $this->order);
    }

    /**
     * Test validation fails with incomplete billing address
     */
    public function testBeforeSaveWithIncompleteBillingAddress(): void
    {
        $this->billingAddress->method('getFirstname')->willReturn('');
        $this->billingAddress->method('getLastname')->willReturn('Doe');

        $this->order->method('getBillingAddress')->willReturn($this->billingAddress);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please provide billing address for the order.');

        $this->plugin->beforeSave($this->orderRepository, $this->order);
    }

    /**
     * Test validation fails with missing order items
     */
    public function testBeforeSaveWithMissingItems(): void
    {
        // Setup valid billing address
        $this->billingAddress->method('getFirstname')->willReturn('John');
        $this->billingAddress->method('getLastname')->willReturn('Doe');
        $this->billingAddress->method('getStreet')->willReturn(['123 Main St']);
        $this->billingAddress->method('getCity')->willReturn('City');
        $this->billingAddress->method('getCountryId')->willReturn('US');

        $this->order->method('getBillingAddress')->willReturn($this->billingAddress);
        $this->order->method('getAllVisibleItems')->willReturn([]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please specify order items.');

        $this->plugin->beforeSave($this->orderRepository, $this->order);
    }
}
