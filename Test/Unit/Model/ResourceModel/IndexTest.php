<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\ResourceModel;

use Magento\Elasticsearch\Model\ResourceModel\Index;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\ResourceModel\Index
     */
    private $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fullText;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productAttributeInterface;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resources;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeInterface;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->storeManager = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
            ])
            ->getMockForAbstractClass();

        $this->storeInterface = $this->getMockBuilder('\Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                'getWebsiteId',
            ])
            ->getMockForAbstractClass();

        $this->productRepository = $this->getMockBuilder('\Magento\Catalog\Api\ProductRepositoryInterface')
            ->getMockForAbstractClass();

        $this->eavConfig = $this->getMockBuilder('\Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getEntityAttributeCodes'])
            ->getMock();

        $this->fullText = $this->getMockBuilder('\Magento\CatalogSearch\Model\ResourceModel\Fulltext')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('\Magento\Framework\Model\ResourceModel\Db\Context')
            ->disableOriginalConstructor()
            ->setMethods([
                'getTransactionManager',
                'getResources',
                'getObjectRelationProcessor',
            ])
            ->getMock();

        $this->eventManager = $this->getMockBuilder('\Magento\Framework\Event\ManagerInterface')
            ->setMethods(['dispatch'])
            ->getMock();

        $this->product = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                'getData',
            ])
            ->getMockForAbstractClass();

        $this->connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods([
                'distinct',
                'from',
                'join',
                'where',
                'orWhere',
            ])
            ->getMock();

        $this->resources = $this->getMockBuilder('\Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->setMethods([
                'getConnection',
                'getTableName',
                'getTablePrefix',
            ])
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

        $this->model = new Index(
            $this->context,
            $this->storeManager,
            $this->productRepository,
            $this->eavConfig,
            'default'
        );
    }

    /**
     * Test getPriceIndexDataEmpty method wich return empty array
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
     * Test getPriceIndexDataEmpty method wich return empty array
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

        $this->assertEquals(
            [
                1 => [
                    1 => 1,
                ],
            ],
            $this->model->getCategoryProductIndexData(1, [1, ])
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
            ->willReturn([1, ]);

        $this->assertEquals([1, ], $this->model->getMovedCategoryProductIds(1));
    }

    /**
     * Test getFullProductIndexData method
     */
    public function testGetFullProductIndexData()
    {
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->willReturn($this->product);

        $this->product->expects($this->once())
            ->method('getData')
            ->willReturn([
                'name' => 'Product Name',
                'category_ids' => [
                    1,
                    2,
                ],
            ]);

        $this->eavConfig->expects($this->once())
            ->method('getEntityAttributeCodes')
            ->with('catalog_product')
            ->willReturn([
                'name',
                'category_ids',
            ]);

        $this->assertEquals(
            [
                1 => [
                    'name' => 'Product Name',
                    'category_ids' => '1,2',
                ],
            ],
            $this->model->getFullProductIndexData([1, ])
        );
    }
}
