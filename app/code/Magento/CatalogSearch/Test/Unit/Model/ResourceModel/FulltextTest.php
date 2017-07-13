<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FulltextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Holder for MetadataPool mock object.
     *
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var Fulltext
     */
    private $target;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getResources')
            ->willReturn($this->resource);
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->target = $objectManager->getObject(
            \Magento\CatalogSearch\Model\ResourceModel\Fulltext::class,
            [
                'context' => $this->context,
                'metadataPool' => $this->metadataPool
            ]
        );
    }

    public function testResetSearchResult()
    {
        $this->resource->expects($this->once())
            ->method('getTableName')
            ->with('search_query', ResourceConnection::DEFAULT_CONNECTION)
            ->willReturn('table_name_search_query');
        $this->connection->expects($this->once())
            ->method('update')
            ->with('table_name_search_query', ['is_processed' => 0], ['is_processed != 0'])
            ->willReturn(10);
        $result = $this->target->resetSearchResults();
        $this->assertEquals($this->target, $result);
    }

    /**
     * @covers \Magento\CatalogSearch\Model\ResourceModel\Fulltext::getRelationsByChild()
     */
    public function testGetRelationsByChild()
    {
        $ids = [1, 2, 3];
        $testTable1 = 'testTable1';
        $testTable2 = 'testTable2';
        $fieldForParent = 'testLinkField';

        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn($fieldForParent);

        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadata);

        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->once())
            ->method('from')
            ->with(['relation' => $testTable1])
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('relation.child_id IN (?)', $ids)
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('join')
            ->with(
                ['cpe' => $testTable2],
                'cpe.' . $fieldForParent . ' = relation.parent_id',
                ['cpe.entity_id']
            )->willReturnSelf();

        $this->connection->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $this->connection->expects($this->once())
            ->method('fetchCol')
            ->with($select)
            ->willReturn($ids);

        $this->resource->expects($this->exactly(2))
            ->method('getTableName')
            ->withConsecutive(
                ['catalog_product_relation', ResourceConnection::DEFAULT_CONNECTION],
                ['catalog_product_entity', ResourceConnection::DEFAULT_CONNECTION]
            )
            ->will($this->onConsecutiveCalls(
                $testTable1,
                $testTable2
            ));

        self::assertSame($ids, $this->target->getRelationsByChild($ids));
    }
}
