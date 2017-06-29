<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Indexer\Attribute;

use Magento\Customer\Model\Indexer\Attribute\Filter;
use Magento\Customer\Api\Data\AttributeMetadataInterface;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $connection;

    /** @var \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $flatScopeResolver;

    /** @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexerRegistry;

    /** @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexer;

    /** @var \Magento\Framework\Indexer\StateInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexerState;

    /** @var Filter */
    protected $model;

    protected function setUp()
    {
        $this->resource = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $this->connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false
        );
        $this->flatScopeResolver = $this->getMock(
            \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver::class,
            [],
            [],
            '',
            false
        );
        $this->indexerRegistry = $this->getMock(\Magento\Framework\Indexer\IndexerRegistry::class, [], [], '', false);
        $this->indexer = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false
        );
        $this->indexerState = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\StateInterface::class,
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
            ->with(\Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID)
            ->willReturn($this->indexer);
        $this->indexer->expects($this->once())
            ->method('getState')
            ->willReturn($this->indexerState);
        $this->indexerState->expects($this->once())
            ->method('getStatus')
            ->willReturn(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID);
        $this->flatScopeResolver->expects($this->once())
            ->method('resolve')
            ->with(\Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID, [])
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
