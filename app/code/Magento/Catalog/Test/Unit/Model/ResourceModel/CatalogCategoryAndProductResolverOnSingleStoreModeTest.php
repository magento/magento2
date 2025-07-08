<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Exception;
use Magento\Catalog\Model\ResourceModel\CatalogCategoryAndProductResolverOnSingleStoreMode as Resolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogCategoryAndProductResolverOnSingleStoreModeTest extends TestCase
{
    /**
     * @var Resolver
     */
    private $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Resolver(
            $this->resourceConnectionMock,
            $this->metadataPoolMock
        );
    }

    /**
     * Test Migrate catalog category and product tables without exception
     */
    public function testCheckMigrateCatalogCategoryAndProductTablesWithoutException(): void
    {
        $catalogProducts = [
            [
                'id' => 1,
                'name' => 'simple1',
                'category_id' => '1',
                'website_id' => '2'
            ],
            [
                'id' => 2,
                'name' => 'simple2',
                'category_id' => '1',
                'website_id' => '2'
            ],
            [
                'id' => 3,
                'name' => 'bundle1',
                'category_id' => '1',
                'website_id' => '2'
            ]
        ];
        $connection = $this->getConnection();
        $connection->expects($this->any())->method('fetchAll')->willReturn($catalogProducts);
        $connection->expects($this->any())->method('delete')->willReturnSelf();
        $connection->expects($this->any())->method('update')->willReturnSelf();
        $connection->expects($this->any())->method('commit')->willReturnSelf();

        $this->model->migrateCatalogCategoryAndProductTables(1);
    }

    /**
     * Test Migrate catalog category and product tables with exception
     */
    public function testCheckMigrateCatalogCategoryAndProductTablesWithException(): void
    {
        $exceptionMessage = 'Exception message';
        $connection = $this->getConnection();
        $connection->expects($this->any())
            ->method('fetchAll')
            ->willThrowException(new Exception($exceptionMessage));
        $connection->expects($this->any())->method('rollBack')->willReturnSelf();
        $this->model->migrateCatalogCategoryAndProductTables(1);
    }

    /**
     * Get connection
     *
     * @return MockObject
     */
    private function getConnection(): MockObject
    {
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $metadata = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->willReturn($metadata);
        $metadata
            ->expects($this->any())
            ->method('getLinkField')
            ->willReturn('row_id');
        $this->resourceConnectionMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);
        $connection->expects($this->once())
            ->method('beginTransaction')
            ->willReturnSelf();
        $this->resourceConnectionMock
            ->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);

        $select = $this->createMock(Select::class);
        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();

        $connection->expects($this->any())->method('select')->willReturn($select);
        return $connection;
    }
}
