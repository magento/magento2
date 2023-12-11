<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product\Flat;

use Magento\Catalog\Helper\Product\Flat\Indexer;
use Magento\Catalog\Model\ResourceModel\ConfigFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Mview\View\Changelog;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Indexer
     */
    protected $_model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var Resource|MockObject
     */
    protected $_resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $_connectionMock;

    /**
     * @var Changelog|MockObject
     */
    protected $_changelogMock;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);

        $this->_resourceMock = $this->createMock(ResourceConnection::class);
        $this->_resourceMock->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $flatHelperMock = $this->createPartialMock(
            Indexer::class,
            ['isAddChildData']
        );
        $flatHelperMock->expects($this->any())->method('isAddChildData')->willReturn(true);

        $eavConfigMock = $this->createMock(Config::class);

        $attributeConfigMock = $this->createMock(\Magento\Catalog\Model\Attribute\Config::class);

        $resourceConfigFactoryMock = $this->createPartialMock(
            ConfigFactory::class,
            ['create']
        );

        $eavFactoryMock = $this->createPartialMock(AttributeFactory::class, ['create']);

        $this->_storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->_connectionMock = $this->createPartialMock(
            Mysql::class,
            ['getTables', 'dropTable']
        );

        $this->_changelogMock = $this->createPartialMock(Changelog::class, ['getName']);

        $this->_objectManager = new ObjectManager($this);
        $this->_model = $this->_objectManager->getObject(
            Indexer::class,
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
            $store = $this->createPartialMock(Store::class, ['getId', '__sleep']);
            $store->expects($this->once())->method('getId')->willReturn($storeId);
            $stores[] = $store;
        }

        $this->_storeManagerMock->expects($this->once())->method('getStores')->willReturn($stores);
    }
}
