<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\MultiDimensionProvider;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    protected $categoryRepository;

    /**
     * @var Config|MockObject
     */
    protected $eavConfig;

    /**
     * @var Fulltext|MockObject
     */
    protected $fullText;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPool;

    /**
     * @var ProductInterface|MockObject
     */
    protected $product;

    /**
     * @var CategoryInterface|MockObject
     */
    protected $category;

    /**
     * @var ProductAttributeInterface|MockObject
     */
    protected $productAttributeInterface;

    /**
     * @var AbstractDb|MockObject
     */
    protected $connection;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $select;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resources;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeInterface;

    /**
     * @var MockObject
     */
    protected $tableResolver;

    /**
     * Setup
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getStore',
            ])
            ->getMockForAbstractClass();

        $this->storeInterface = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getWebsiteId',
            ])
            ->getMockForAbstractClass();

        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->categoryRepository = $this->getMockBuilder(CategoryRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getEntityAttributeCodes',
                'getAttribute',
            ])
            ->getMock();

        $this->fullText = $this->getMockBuilder(Fulltext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getTransactionManager',
                'getResources',
                'getObjectRelationProcessor',
            ])
            ->getMock();

        $this->eventManager = $this->getMockBuilder(ManagerInterface::class)
            ->onlyMethods(['dispatch'])
            ->getMockForAbstractClass();

        $this->product = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getData',
            ])
            ->getMockForAbstractClass();

        $this->category = $this->getMockBuilder(CategoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getName',
            ])
            ->getMockForAbstractClass();

        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'distinct',
                'from',
                'join',
                'where',
                'orWhere',
            ])
            ->getMock();

        $this->resources = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getConnection',
                'getTableName',
                'getTablePrefix',
            ])
            ->getMock();

        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIdentifierField'])
            ->onlyMethods(['getMetadata'])
            ->getMock();

        $this->context->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resources);

        $this->resources->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->resources->expects($this->any())
            ->method('getTablePrefix')
            ->willReturn('');

        $this->metadataPool->method('getMetadata')
            ->willReturnSelf();
        $this->metadataPool->method('getIdentifierField')
            ->willReturn('entity_id');

        $objectManager = new ObjectManagerHelper($this);

        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->onlyMethods([
                'getConnection',
                'getTableName'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);
        $resource->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $this->tableResolver = $objectManager->getObject(
            IndexScopeResolver::class,
            [
                'resource' => $resource
            ]
        );

        $traversableMock = $this->createMock(\Traversable::class);
        $dimensionsMock = $this->createMock(MultiDimensionProvider::class);
        $dimensionsMock->method('getIterator')->willReturn($traversableMock);

        $indexScopeResolverMock = $this->createMock(
            IndexScopeResolverInterface::class
        );

        $dimensionFactoryMock = $this->createMock(
            DimensionCollectionFactory::class
        );
        $dimensionFactoryMock->method('create')->willReturn($dimensionsMock);
        $indexScopeResolverMock->method('resolve')->willReturn('catalog_product_index_price');

        $this->model = $objectManager->getObject(
            Index::class,
            [
                'context' => $this->context,
                'storeManager' => $this->storeManager,
                'metadataPool' => $this->metadataPool,
                'productRepository' => $this->productRepository,
                'categoryRepository' => $this->categoryRepository,
                'eavConfig' => $this->eavConfig,
                'connectionName' => 'default',
                'tableResolver' => $this->tableResolver,
                'dimensionCollectionFactory' => $dimensionFactoryMock,
            ]
        );
    }

    /**
     * Test getPriceIndexDataEmpty method which return empty array
     */
    public function testGetPriceIndexData()
    {
        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([[
                'website_id' => 1,
                'entity_id' => 1,
                'customer_group_id' => 1,
                'min_price' => 1,
            ]]);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeInterface);

        $this->storeInterface->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->assertEquals(
            [
                1 => [
                    1 => 1,
                ],
            ],
            $this->model->getPriceIndexData([1 ], 1)
        );
    }

    /**
     * Test getPriceIndexDataEmpty method which return empty array
     */
    public function testGetPriceIndexDataEmpty()
    {
        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([]);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeInterface);

        $this->storeInterface->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->assertEquals(
            [],
            $this->model->getPriceIndexData([1 ], 1)
        );
    }

    /**
     * Test getCategoryProductIndexData method
     */
    public function testGetCategoryProductIndexData()
    {
        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('from')
            ->with(
                ['catalog_category_product_index_store1'],
                ['category_id', 'product_id', 'position', 'store_id']
            )->willReturnSelf();

        $select->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([[
                'product_id' => 1,
                'category_id' => 1,
                'position' => 1,
            ]]);

        $this->assertEquals(
            [
                1 => [
                    1 => 1,
                ],
            ],
            $this->model->getCategoryProductIndexData(1, [1])
        );
    }

    /**
     * Test getMovedCategoryProductIds method
     */
    public function testGetMovedCategoryProductIds()
    {
        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('distinct')
            ->willReturnSelf();

        $this->resources->expects($this->exactly(2))
            ->method('getTableName');

        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('join')
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('orWhere')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchCol')
            ->with($select)
            ->willReturn([1]);

        $this->assertEquals([1], $this->model->getMovedCategoryProductIds(1));
    }

    /**
     * Test getFullProductIndexData method
     *
     * @param string $frontendInput
     * @param mixed $indexData
     * @return void
     * @dataProvider attributeCodeProvider
     */
    public function testGetFullProductIndexData($frontendInput, $indexData)
    {
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->willReturn($this->product);

        $this->product->expects($this->once())
            ->method('getData')
            ->willReturn([
                'name' => 'Product Name'
            ]);

        $this->eavConfig->expects($this->once())
            ->method('getEntityAttributeCodes')
            ->with('catalog_product')
            ->willReturn([
                'name',
            ]);

        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getFrontendInput',
                'getOptions',
                'getData',
                'getAttributeId',
            ])
            ->getMock();

        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with('catalog_product', 'name')
            ->willReturn($attributeMock);

        $attributeMock->expects($this->any())
            ->method('getAttributeId')
            ->willReturn(1);

        $attributeMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);

        $attributeOption = $this->createMock(Option::class);
        $attributeOption->expects($this->any())->method('getValue')->willReturn('240-LV04');
        $attributeOption->expects($this->any())->method('getLabel')->willReturn('label');

        $attributeMock->expects($this->any())
            ->method('getOptions')
            ->willReturn([$attributeOption]);

        $this->assertIsArray($this->model->getFullProductIndexData(
            1,
            [
                1 => $indexData
            ]
        ));
    }

    /**
     * Test getFullCategoryProductIndexData method
     */
    public function testGetFullCategoryProductIndexData()
    {
        $this->categoryRepository->expects($this->once())
            ->method('get')
            ->willReturn($this->category);

        $this->category->expects($this->once())
            ->method('getName')
            ->willReturn([
                'name' => 'Category Name',
            ]);

        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([[
                'product_id' => 1,
                'category_id' => 1,
                'position' => 1,
            ]]);

        $this->assertIsArray($this->model->getFullCategoryProductIndexData(1, [1]));
    }

    /**
     * Provides data for testGetFullProductIndexData method.
     *
     * @return array
     */
    public static function attributeCodeProvider()
    {
        return [
            [
                'string',
                '240-LV04',
            ],
            [
                'select',
                '240-LV04',
            ],
            [
                'select',
                [1],
            ],
            [
                'select',
                [
                    1 => 1,
                ],
            ]
        ];
    }
}
