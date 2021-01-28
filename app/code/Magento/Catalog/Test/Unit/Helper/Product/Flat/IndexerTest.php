<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Helper\Product\Flat;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var Resource|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_connectionMock;

    /**
     * @var \Magento\Framework\Mview\View\Changelog|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_changelogMock;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(\Magento\Framework\App\Helper\Context::class);

        $this->_resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->_resourceMock->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $flatHelperMock = $this->createPartialMock(
            \Magento\Catalog\Helper\Product\Flat\Indexer::class,
            ['isAddChildData']
        );
        $flatHelperMock->expects($this->any())->method('isAddChildData')->willReturn(true);

        $eavConfigMock = $this->createMock(\Magento\Eav\Model\Config::class);

        $attributeConfigMock = $this->createMock(\Magento\Catalog\Model\Attribute\Config::class);

        $resourceConfigFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\ConfigFactory::class,
            ['create']
        );

        $eavFactoryMock = $this->createPartialMock(\Magento\Eav\Model\Entity\AttributeFactory::class, ['create']);

        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->_connectionMock = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['getTables', 'dropTable']
        );

        $this->_changelogMock = $this->createPartialMock(\Magento\Framework\Mview\View\Changelog::class, ['getName']);

        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $this->_objectManager->getObject(
            \Magento\Catalog\Helper\Product\Flat\Indexer::class,
            [
                'context' => $contextMock,
                'resource' => $this->_resourceMock,
                'flatHelper' => $flatHelperMock,
                'eavConfig' => $eavConfigMock,
                'attributeConfig' => $attributeConfigMock,
                'configFactory' => $resourceConfigFactoryMock,
                'attributeFactory' => $eavFactoryMock,
                'storeManager' => $this->_storeManagerMock,
                'changelog' => $this->_changelogMock,
                'flatAttributeGroups' => ['catalog_product']
            ]
        );
    }

    public function testGetFlatColumnsDdlDefinition()
    {
        foreach ($this->_model->getFlatColumnsDdlDefinition() as $column) {
            $this->assertIsArray($column, 'Columns must be an array value');
            $this->assertArrayHasKey('type', $column, 'Column must have type definition at least');
        }
    }

    public function testGetFlatTableName()
    {
        $storeId = 1;
        $this->assertEquals('catalog_product_flat_1', $this->_model->getFlatTableName($storeId));
    }

    /**
     * Test deleting non-existent stores flat tables
     */
    public function testDeleteAbandonedStoreFlatTables()
    {
        $this->_changelogMock->expects(
            $this->any()
        )->method(
            'getName'
        )->willReturn(
            'catalog_product_flat_cl'
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'getTables'
        )->with(
            'catalog_product_flat_%'
        )->willReturn(
            ['catalog_product_flat_1', 'catalog_product_flat_2', 'catalog_product_flat_3']
        );

        $this->_connectionMock->expects($this->once())->method('dropTable')->with('catalog_product_flat_3');

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->willReturn(
            $this->_connectionMock
        );

        $this->_setStoreManagerExpectedStores([1, 2]);

        $this->_model->deleteAbandonedStoreFlatTables();
    }

    /**
     * Test deleting multiple non-existent stores tables with changelog table
     */
    public function testDeleteNoStoresTables()
    {
        $this->_changelogMock->expects(
            $this->any()
        )->method(
            'getName'
        )->willReturn(
            'catalog_product_flat_cl'
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'getTables'
        )->with(
            'catalog_product_flat_%'
        )->willReturn(
            
                [
                    'catalog_product_flat_1',
                    'catalog_product_flat_2',
                    'catalog_product_flat_3',
                    'catalog_product_flat_4',
                    'catalog_product_flat_cl',
                ]
            
        );

        $this->_connectionMock->expects($this->exactly(3))->method('dropTable');

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->willReturn(
            $this->_connectionMock
        );

        $this->_setStoreManagerExpectedStores([1]);

        $this->_model->deleteAbandonedStoreFlatTables();
    }

    /**
     * Test deleting changelog table
     */
    public function testDeleteCl()
    {
        $this->_changelogMock->expects(
            $this->any()
        )->method(
            'getName'
        )->willReturn(
            'catalog_product_flat_cl'
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'getTables'
        )->with(
            'catalog_product_flat_%'
        )->willReturn(
            ['catalog_product_flat_cl']
        );

        $this->_connectionMock->expects($this->never())->method('dropTable');

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->willReturn(
            $this->_connectionMock
        );

        $this->_setStoreManagerExpectedStores([1]);

        $this->_model->deleteAbandonedStoreFlatTables();
    }

    /**
     * Initialize store manager mock with expected store IDs
     *
     * @param array $storeIds
     */
    protected function _setStoreManagerExpectedStores(array $storeIds)
    {
        $stores = [];
        foreach ($storeIds as $storeId) {
            $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId', '__sleep', '__wakeup']);
            $store->expects($this->once())->method('getId')->willReturn($storeId);
            $stores[] = $store;
        }

        $this->_storeManagerMock->expects($this->once())->method('getStores')->willReturn($stores);
    }
}
