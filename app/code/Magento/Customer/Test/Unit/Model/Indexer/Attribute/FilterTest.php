<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Indexer\Attribute;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Indexer\Attribute\Filter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use Magento\Framework\Indexer\StateInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    /** @var ResourceConnection|MockObject */
    protected $resource;

    /** @var AdapterInterface|MockObject */
    protected $connection;

    /** @var FlatScopeResolver|MockObject */
    protected $flatScopeResolver;

    /** @var IndexerRegistry|MockObject */
    protected $indexerRegistry;

    /** @var IndexerInterface|MockObject */
    protected $indexer;

    /** @var StateInterface|MockObject */
    protected $indexerState;

    /** @var Filter */
    protected $model;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false
        );
        $this->flatScopeResolver = $this->createMock(FlatScopeResolver::class);
        $this->indexerRegistry = $this->createMock(IndexerRegistry::class);
        $this->indexer = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false
        );
        $this->indexerState = $this->getMockForAbstractClass(
            StateInterface::class,
            [],
            '',
            false
        );

        $this->model = new Filter(
            $this->resource,
            $this->flatScopeResolver,
            $this->indexerRegistry
        );
    }

    public function testFilter()
    {
        $attributeCode = 'attribute-code';
        $attributeCodeTwo = 'attribute-code2';
        $tableName = 'customer_grid_flat';

        $attributes = [
            $attributeCode => [
                AttributeMetadataInterface::ATTRIBUTE_CODE => $attributeCode,
                AttributeMetadataInterface::FRONTEND_INPUT => 'input',
                AttributeMetadataInterface::FRONTEND_LABEL => 'Frontend label',
                AttributeMetadataInterface::BACKEND_TYPE => 'static',
                AttributeMetadataInterface::OPTIONS => [],
                AttributeMetadataInterface::IS_USED_IN_GRID => true,
                AttributeMetadataInterface::IS_VISIBLE_IN_GRID => true,
                AttributeMetadataInterface::IS_FILTERABLE_IN_GRID => true,
                AttributeMetadataInterface::IS_SEARCHABLE_IN_GRID => true,
            ],
            $attributeCodeTwo => [
                AttributeMetadataInterface::ATTRIBUTE_CODE => $attributeCodeTwo,
                AttributeMetadataInterface::FRONTEND_INPUT => 'input',
                AttributeMetadataInterface::FRONTEND_LABEL => 'Frontend label two',
                AttributeMetadataInterface::BACKEND_TYPE => 'static',
                AttributeMetadataInterface::OPTIONS => [],
                AttributeMetadataInterface::IS_USED_IN_GRID => false,
                AttributeMetadataInterface::IS_VISIBLE_IN_GRID => false,
                AttributeMetadataInterface::IS_FILTERABLE_IN_GRID => false,
                AttributeMetadataInterface::IS_SEARCHABLE_IN_GRID => false,
            ]
        ];

        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with(Customer::CUSTOMER_GRID_INDEXER_ID)
            ->willReturn($this->indexer);
        $this->indexer->expects($this->once())
            ->method('getState')
            ->willReturn($this->indexerState);
        $this->indexerState->expects($this->once())
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_INVALID);
        $this->flatScopeResolver->expects($this->once())
            ->method('resolve')
            ->with(Customer::CUSTOMER_GRID_INDEXER_ID, [])
            ->willReturn($tableName);
        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->connection->expects($this->once())
            ->method('describeTable')
            ->with($tableName)
            ->willReturn([
                'attribute-code' => ['Attribute data']
            ]);

        $this->assertArrayNotHasKey($attributeCodeTwo, $this->model->filter($attributes));
    }
}
