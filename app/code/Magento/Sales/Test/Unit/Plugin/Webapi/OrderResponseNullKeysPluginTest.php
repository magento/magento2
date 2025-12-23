<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Plugin\Webapi;

use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Plugin\Webapi\OrderResponseNullKeysPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderResponseNullKeysPluginTest extends TestCase
{
    /** @var ServiceOutputProcessor|MockObject */
    private $serviceOutputProcessor;

    /** @var OrderResponseNullKeysPlugin */
    private $plugin;

    protected function setUp(): void
    {
        $this->serviceOutputProcessor = $this->createMock(ServiceOutputProcessor::class);
        $this->plugin = new OrderResponseNullKeysPlugin();
    }

    public function testAfterProcessNonOrderRepositoryReturnsUnchanged(): void
    {
        $input = ['foo' => 'bar'];

        $result = $this->plugin->afterProcess(
            $this->serviceOutputProcessor,
            $input,
            [],
            \Magento\Catalog\Api\ProductRepositoryInterface::class,
            'get'
        );

        $this->assertSame($input, $result);
    }

    public function testAfterProcessGetAddsNullKeysWhenMissing(): void
    {
        $input = ['entity_id' => 10];

        $result = $this->plugin->afterProcess(
            $this->serviceOutputProcessor,
            $input,
            [],
            OrderRepositoryInterface::class,
            'get'
        );

        $this->assertArrayHasKey('entity_id', $result);
        $this->assertSame(10, $result['entity_id']);
        $this->assertArrayHasKey('state', $result);
        $this->assertNull($result['state']);
        $this->assertArrayHasKey('status', $result);
        $this->assertNull($result['status']);
    }

    public function testAfterProcessGetDoesNotOverwriteExistingKeys(): void
    {
        $input = ['entity_id' => 10, 'state' => 'processing', 'status' => 'processing'];

        $result = $this->plugin->afterProcess(
            $this->serviceOutputProcessor,
            $input,
            [],
            OrderRepositoryInterface::class,
            'get'
        );

        $this->assertSame('processing', $result['state']);
        $this->assertSame('processing', $result['status']);
    }

    public function testAfterProcessGetListAddsNullKeysPerItem(): void
    {
        $input = [
            'items' => [
                ['entity_id' => 1, 'state' => 'processing', 'status' => 'processing'],
                ['entity_id' => 2],
                ['entity_id' => 3, 'state' => null], // state exists (null); status missing
            ],
            'search_criteria' => [],
            'total_count' => 3,
        ];

        $result = $this->plugin->afterProcess(
            $this->serviceOutputProcessor,
            $input,
            [],
            OrderRepositoryInterface::class,
            'getList'
        );

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(3, $result['items']);

        // Item 0 unchanged
        $this->assertSame('processing', $result['items'][0]['state']);
        $this->assertSame('processing', $result['items'][0]['status']);

        // Item 1 added nulls
        $this->assertArrayHasKey('state', $result['items'][1]);
        $this->assertNull($result['items'][1]['state']);
        $this->assertArrayHasKey('status', $result['items'][1]);
        $this->assertNull($result['items'][1]['status']);

        // Item 2 keeps existing state (null) and adds missing status
        $this->assertArrayHasKey('state', $result['items'][2]);
        $this->assertNull($result['items'][2]['state']);
        $this->assertArrayHasKey('status', $result['items'][2]);
        $this->assertNull($result['items'][2]['status']);
    }

    public function testAfterProcessGetListWithoutItemsRemainsUnchanged(): void
    {
        $input = ['search_criteria' => [], 'total_count' => 0];

        $result = $this->plugin->afterProcess(
            $this->serviceOutputProcessor,
            $input,
            [],
            OrderRepositoryInterface::class,
            'getList'
        );

        $this->assertSame($input, $result);
    }
}
