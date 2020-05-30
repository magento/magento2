<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Row;
use Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowTest extends TestCase
{
    /**
     * @var Row
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $storeManager;

    /**
     * @var MockObject
     */
    protected $store;

    /**
     * @var MockObject
     */
    protected $productIndexerHelper;

    /**
     * @var MockObject
     */
    protected $resource;

    /**
     * @var MockObject
     */
    protected $connection;

    /**
     * @var MockObject
     */
    protected $flatItemWriter;

    /**
     * @var MockObject
     */
    protected $flatItemEraser;

    /**
     * @var MockObject
     */
    protected $flatTableBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $attributeTable = 'catalog_product_entity_int';
        $statusId = 22;
        $this->connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->resource->expects($this->any())->method('getConnection')
            ->with('default')
            ->willReturn($this->connection);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->store = $this->createMock(Store::class);
        $this->store->expects($this->any())->method('getId')->willReturn('store_id_1');
        $this->storeManager->expects($this->any())->method('getStores')->willReturn([$this->store]);
        $this->flatItemEraser = $this->createMock(Eraser::class);
        $this->flatItemWriter = $this->createMock(Indexer::class);
        $this->flatTableBuilder = $this->createMock(
            FlatTableBuilder::class
        );
        $this->productIndexerHelper = $this->createMock(\Magento\Catalog\Helper\Product\Flat\Indexer::class);
        $statusAttributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productIndexerHelper->expects($this->any())->method('getAttribute')
            ->with('status')
            ->willReturn($statusAttributeMock);
        $backendMock = $this->getMockBuilder(AbstractBackend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backendMock->expects($this->any())->method('getTable')->willReturn($attributeTable);
        $statusAttributeMock->expects($this->any())->method('getBackend')->willReturn($backendMock);
        $statusAttributeMock->expects($this->any())->method('getId')->willReturn($statusId);
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->any())->method('select')->willReturn($selectMock);
        $selectMock->method('from')
            ->willReturnSelf();
        $selectMock->method('joinLeft')
            ->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('order')->willReturnSelf();
        $selectMock->expects($this->any())->method('limit')->willReturnSelf();
        $pdoMock = $this->createMock(\Zend_Db_Statement_Pdo::class);
        $this->connection->expects($this->any())->method('query')->with($selectMock)->willReturn($pdoMock);
        $pdoMock->expects($this->any())->method('fetchColumn')->willReturn('1');

        $metadataPool = $this->createMock(MetadataPool::class);
        $productMetadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $metadataPool->expects($this->any())->method('getMetadata')->with(ProductInterface::class)
            ->willReturn($productMetadata);
        $productMetadata->expects($this->any())->method('getLinkField')->willReturn('entity_id');

        $this->model = $objectManager->getObject(
            Row::class,
            [
                'resource'         => $this->resource,
                'storeManager'     => $this->storeManager,
                'productHelper'    => $this->productIndexerHelper,
                'flatItemEraser'   => $this->flatItemEraser,
                'flatItemWriter'   => $this->flatItemWriter,
                'flatTableBuilder' => $this->flatTableBuilder,
            ]
        );

        $objectManager->setBackwardCompatibleProperty($this->model, 'metadataPool', $metadataPool);
    }

    public function testExecuteWithEmptyId()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('We can\'t rebuild the index for an undefined product.');
        $this->model->execute(null);
    }

    public function testExecuteWithNonExistingFlatTablesCreatesTables()
    {
        $this->productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->willReturn('store_flat_table');
        $this->connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->willReturn(false);
        $this->flatItemEraser->expects($this->never())->method('removeDeletedProducts');
        $this->flatTableBuilder->expects($this->once())->method('build')->with('store_id_1', ['product_id_1']);
        $this->flatItemWriter->expects($this->once())->method('write')->with('store_id_1', 'product_id_1');
        $this->model->execute('product_id_1');
    }

    public function testExecuteWithExistingFlatTablesCreatesTables()
    {
        $this->productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->willReturn('store_flat_table');
        $this->connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->willReturn(true);
        $this->connection->expects($this->any())->method('fetchCol')
            ->willReturn(['store_id_1']);
        $this->flatItemEraser->expects($this->once())->method('removeDeletedProducts');
        $this->flatTableBuilder->expects($this->never())->method('build')->with('store_id_1', ['product_id_1']);
        $this->model->execute('product_id_1');
    }

    public function testExecuteWithExistingFlatTablesRemoveProductFromStore()
    {
        $this->productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->willReturn('store_flat_table');
        $this->connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->willReturn(true);
        $this->connection->expects($this->any())->method('fetchCol')
            ->willReturn([1]);
        $this->flatItemEraser->expects($this->once())->method('deleteProductsFromStore');
        $this->flatItemEraser->expects($this->never())->method('removeDeletedProducts');
        $this->flatTableBuilder->expects($this->never())->method('build')->with('store_id_1', ['product_id_1']);
        $this->model->execute('product_id_1');
    }
}
