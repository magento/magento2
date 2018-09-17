<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogSearch\Model\Indexer\ParentProductsResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ParentProductsResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadata;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\ParentProductsResolver
     */
    private $resolver;

    protected function setUp()
    {
        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->metadata);

        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'getIfNullSql', 'quote'])
            ->getMockForAbstractClass();

        $this->resourceConnection->expects($this->atLeastOnce())
            ->method('getConnection')
            ->will($this->returnValue($this->connection));

        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from', 'distinct', 'where', 'join', '__toString'])
            ->getMock();
        $this->connection->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->select));

        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(
            ParentProductsResolver::class,
            [
                'resourceConnection' => $this->resourceConnection,
                'metadataPool' => $this->metadataPool,
            ]
        );
    }

    public function testGetParentProductIds()
    {
        $childProductIds = [12, 13, 14];

        $this->metadata->expects(self::once())
            ->method('getLinkField')
            ->willReturn('product_link_field');
        $this->resourceConnection->expects($this->any())
            ->method('getTableName')
            ->willReturnMap(
                [
                    ['catalog_product_relation', ResourceConnection::DEFAULT_CONNECTION, 'relation_table'],
                    ['catalog_product_entity', ResourceConnection::DEFAULT_CONNECTION, 'product_table'],
                ]
            );
        $this->select->expects(self::once())->method('from')
            ->with(['relation' => 'relation_table'], [], null)
            ->willReturnSelf();
        $this->select->expects(self::once())->method('distinct')
            ->with(true)
            ->willReturnSelf();
        $this->select->expects(self::once())->method('where')
            ->withConsecutive(
                [$this->equalTo('child_id IN (?)'), $this->equalTo($childProductIds)]
            )
            ->willReturnSelf();
        $this->select->expects(self::once())->method('join')
            ->with(
                ['cpe' => 'product_table'],
                'relation.parent_id = cpe.product_link_field',
                ['cpe.entity_id']
            )
            ->willReturnSelf();
        $this->resolver->getParentProductIds($childProductIds);
    }
}
