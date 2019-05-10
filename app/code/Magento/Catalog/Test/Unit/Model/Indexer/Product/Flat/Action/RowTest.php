<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row
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
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $attributeTable = 'catalog_product_entity_int';
        $statusId = 22;
        $this->connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resource->expects($this->any())->method('getConnection')
            ->with('default')
            ->willReturn($this->connection);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->store->expects($this->any())->method('getId')->willReturn('store_id_1');
        $this->storeManager->expects($this->any())->method('getStores')->willReturn([$this->store]);
        $this->flatItemEraser = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser::class);
        $this->flatItemWriter = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer::class);
        $this->flatTableBuilder = $this->createMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder::class
        );
        $this->productIndexerHelper = $this->createMock(\Magento\Catalog\Helper\Product\Flat\Indexer::class);
        $statusAttributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productIndexerHelper->expects($this->any())->method('getAttribute')
            ->with('status')
            ->willReturn($statusAttributeMock);
        $backendMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backendMock->expects($this->any())->method('getTable')->willReturn($attributeTable);
        $statusAttributeMock->expects($this->any())->method('getBackend')->willReturn($backendMock);
        $statusAttributeMock->expects($this->any())->method('getId')->willReturn($statusId);
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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

        $metadataPool = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);
        $productMetadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $metadataPool->expects($this->any())->method('getMetadata')->with(ProductInterface::class)
            ->willReturn($productMetadata);
        $productMetadata->expects($this->any())->method('getLinkField')->willReturn('entity_id');

        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row::class,
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

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We can't rebuild the index for an undefined product.
     */
    public function testExecuteWithEmptyId()
    {
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
        $this->flatItemEraser->expects($this->once())->method('removeDeletedProducts');
        $this->flatTableBuilder->expects($this->never())->method('build')->with('store_id_1', ['product_id_1']);
        $this->model->execute('product_id_1');
    }
}
