<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product\Flat\Indexer as FlatIndexerHelper;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Row;
use Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ResourceModel\Store\Collection;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowTest extends TestCase
{
    private const STUB_ATTRIBUTE_TABLE = 'catalog_product_entity_int';
    private const STUB_STATUS_ID = 22;

    /**
     * @var Row
     */
    private $model;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var FlatIndexerHelper|MockObject
     */
    private $productIndexerHelperMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Indexer|MockObject
     */
    private $flatItemWriterMock;

    /**
     * @var Eraser|MockObject
     */
    private $flatItemEraserMock;

    /**
     * @var FlatTableBuilder|MockObject
     */
    private $flatTableBuilderMock;

    /**
     * @var Collection|MockObject
     */
    private $storeCollectionMock;

    /**
     * @var Link|MockObject
     */
    private $productWebsiteLinkMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->method('getConnection')
            ->with('default')
            ->willReturn($this->connectionMock);
        $this->storeMock = $this->createMock(Store::class);
        $this->storeMock->method('getId')
            ->willReturn('store_id_1');
        $this->flatItemEraserMock = $this->createMock(Eraser::class);
        $this->flatItemWriterMock = $this->createMock(Indexer::class);
        $this->flatTableBuilderMock = $this->createMock(FlatTableBuilder::class);
        $this->productIndexerHelperMock = $this->createMock(FlatIndexerHelper::class);
        $statusAttributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productIndexerHelperMock->method('getAttribute')
            ->with('status')
            ->willReturn($statusAttributeMock);
        $this->productWebsiteLinkMock = $this->createMock(Link::class);
        $this->storeCollectionMock = $this->createMock(Collection::class);
        $this->storeCollectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->storeMock]));
        $storesCollectionFactory = $this->createMock(CollectionFactory::class);
        $storesCollectionFactory->method('create')
            ->willReturn($this->storeCollectionMock);
        $backendMock = $this->getMockBuilder(AbstractBackend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backendMock->method('getTable')
            ->willReturn(self::STUB_ATTRIBUTE_TABLE);
        $statusAttributeMock->method('getBackend')
            ->willReturn($backendMock);
        $statusAttributeMock->method('getId')
            ->willReturn(self::STUB_STATUS_ID);

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->method('select')
            ->willReturn($selectMock);
        $selectMock->method('from')
            ->willReturnSelf();
        $selectMock->method('joinLeft')
            ->willReturnSelf();
        $selectMock->method('where')
            ->willReturnSelf();
        $selectMock->method('order')
            ->willReturnSelf();
        $selectMock->method('limit')
            ->willReturnSelf();
        $pdoMock = $this->createMock(\Zend_Db_Statement_Pdo::class);
        $this->connectionMock->method('query')
            ->with($selectMock)
            ->willReturn($pdoMock);
        $pdoMock->method('fetchColumn')
            ->willReturn('1');

        $metadataPool = $this->createMock(MetadataPool::class);
        $productMetadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $metadataPool->method('getMetadata')->with(ProductInterface::class)
            ->willReturn($productMetadata);
        $productMetadata->method('getLinkField')->willReturn('entity_id');

        $this->model = $objectManager->getObject(
            Row::class,
            [
                'resource'         => $this->resourceMock,
                'productHelper'    => $this->productIndexerHelperMock,
                'flatItemEraser'   => $this->flatItemEraserMock,
                'flatItemWriter'   => $this->flatItemWriterMock,
                'flatTableBuilder' => $this->flatTableBuilderMock,
                'productWebsiteLink' => $this->productWebsiteLinkMock,
                'storeCollectionFactory' => $storesCollectionFactory,
            ]
        );

        $objectManager->setBackwardCompatibleProperty($this->model, 'metadataPool', $metadataPool);
    }

    /**
     * Execute with id null
     *
     * @return void
     */
    public function testExecuteWithEmptyId(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('We can\'t rebuild the index for an undefined product.');

        $this->model->execute(null);
    }

    /**
     * Execute flat table not exist
     *
     * @return void
     */
    public function testExecuteWithNonExistingFlatTablesCreatesTables(): void
    {
        $this->productIndexerHelperMock->expects($this->once())->method('getFlatTableName')
            ->willReturn('store_flat_table');
        $this->connectionMock->expects($this->once())
            ->method('isTableExists')
            ->with('store_flat_table')
            ->willReturn(false);
        $this->flatItemEraserMock->expects($this->never())
            ->method('removeDeletedProducts');
        $this->flatTableBuilderMock->expects($this->once())
            ->method('build')
            ->with('store_id_1', ['product_id_1']);
        $this->flatItemWriterMock->expects($this->once())
            ->method('write')
            ->with('store_id_1', 'product_id_1');
        $this->productWebsiteLinkMock->expects($this->once())
            ->method('getWebsiteIdsByProductId')
            ->willReturn([]);
        $this->storeCollectionMock->expects($this->once())
            ->method('addWebsiteFilter')
            ->willReturnSelf();

        $this->model->execute('product_id_1');
    }

    /**
     * Execute flat table exist
     *
     * @return void
     */
    public function testExecuteWithExistingFlatTablesCreatesTables(): void
    {
        $this->productIndexerHelperMock->expects($this->once())
            ->method('getFlatTableName')
            ->willReturn('store_flat_table');
        $this->connectionMock->expects($this->once())
            ->method('isTableExists')
            ->with('store_flat_table')
            ->willReturn(true);
        $this->flatItemEraserMock->expects($this->once())
            ->method('removeDeletedProducts');
        $this->flatTableBuilderMock->expects($this->never())
            ->method('build')
            ->with('store_id_1', ['product_id_1']);
        $this->productWebsiteLinkMock->expects($this->once())
            ->method('getWebsiteIdsByProductId')
            ->willReturn([]);
        $this->storeCollectionMock->expects($this->once())
            ->method('addWebsiteFilter')
            ->willReturnSelf();

        $this->model->execute('product_id_1');
    }
}
