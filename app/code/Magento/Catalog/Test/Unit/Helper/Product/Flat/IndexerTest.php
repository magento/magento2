<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Helper\Product\Flat;

use Magento\Framework\App\ResourceConnection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connectionMock;

    /**
     * @var \Magento\Framework\Mview\View\Changelog|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_changelogMock;

    protected function setUp()
    {
        $contextMock = $this->getMock(\Magento\Framework\App\Helper\Context::class, [], [], '', false);

        $this->_resourceMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );
        $this->_resourceMock->expects($this->any())->method('getTableName')->will($this->returnArgument(0));

        $flatHelperMock = $this->getMock(
            \Magento\Catalog\Helper\Product\Flat\Indexer::class,
            ['isAddChildData'],
            [],
            '',
            false
        );
        $flatHelperMock->expects($this->any())->method('isAddChildData')->will($this->returnValue(true));

        $eavConfigMock = $this->getMock(\Magento\Eav\Model\Config::class, [], [], '', false);

        $attributeConfigMock = $this->getMock(\Magento\Catalog\Model\Attribute\Config::class, [], [], '', false);

        $resourceConfigFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\ConfigFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $eavFactoryMock = $this->getMock(\Magento\Eav\Model\Entity\AttributeFactory::class, ['create'], [], '', false);

        $this->_storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->_connectionMock = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['getTables', 'dropTable'],
            [],
            '',
            false
        );

        $this->_changelogMock = $this->getMock(
            \Magento\Framework\Mview\View\Changelog::class,
            ['getName'],
            [],
            '',
            false
        );

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
            $this->assertTrue(is_array($column), 'Columns must be an array value');
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
        )->will(
            $this->returnValue('catalog_product_flat_cl')
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'getTables'
        )->with(
            'catalog_product_flat_%'
        )->will(
            $this->returnValue(['catalog_product_flat_1', 'catalog_product_flat_2', 'catalog_product_flat_3'])
        );

        $this->_connectionMock->expects($this->once())->method('dropTable')->with('catalog_product_flat_3');

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->_connectionMock)
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
        )->will(
            $this->returnValue('catalog_product_flat_cl')
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'getTables'
        )->with(
            'catalog_product_flat_%'
        )->will(
            $this->returnValue(
                [
                    'catalog_product_flat_1',
                    'catalog_product_flat_2',
                    'catalog_product_flat_3',
                    'catalog_product_flat_4',
                    'catalog_product_flat_cl',
                ]
            )
        );

        $this->_connectionMock->expects($this->exactly(3))->method('dropTable');

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->_connectionMock)
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
        )->will(
            $this->returnValue('catalog_product_flat_cl')
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'getTables'
        )->with(
            'catalog_product_flat_%'
        )->will(
            $this->returnValue(['catalog_product_flat_cl'])
        );

        $this->_connectionMock->expects($this->never())->method('dropTable');

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->_connectionMock)
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
            $store = $this->getMock(
                \Magento\Store\Model\Store::class,
                ['getId', '__sleep', '__wakeup'],
                [],
                '',
                false
            );
            $store->expects($this->once())->method('getId')->will($this->returnValue($storeId));
            $stores[] = $store;
        }

        $this->_storeManagerMock->expects($this->once())->method('getStores')->will($this->returnValue($stores));
    }
}
