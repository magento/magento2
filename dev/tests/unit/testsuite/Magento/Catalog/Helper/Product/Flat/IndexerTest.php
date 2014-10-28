<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Helper\Product\Flat;

class IndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_model;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connectionMock;

    /**
     * @var \Magento\Framework\Mview\View\Changelog|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_changelogMock;

    public function setUp()
    {
        $contextMock = $this->getMock('Magento\Framework\App\Helper\Context', array(), array(), '', false);

        $this->_resourceMock = $this->getMock(
            'Magento\Framework\App\Resource',
            array('getTableName', 'getConnection'),
            array(),
            '',
            false
        );
        $this->_resourceMock->expects($this->any())->method('getTableName')->will($this->returnArgument(0));

        $flatHelperMock = $this->getMock(
            'Magento\Catalog\Helper\Product\Flat\Indexer',
            array('isAddChildData'),
            array(),
            '',
            false
        );
        $flatHelperMock->expects($this->any())->method('isAddChildData')->will($this->returnValue(true));

        $eavConfigMock = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);

        $attributeConfigMock = $this->getMock('Magento\Catalog\Model\Attribute\Config', array(), array(), '', false);

        $resourceConfigFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Resource\ConfigFactory',
            array(),
            array(),
            '',
            false
        );

        $eavFactoryMock = $this->getMock('Magento\Eav\Model\Entity\AttributeFactory', array(), array(), '', false);

        $this->_storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');

        $this->_connectionMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('getTables', 'dropTable'),
            array(),
            '',
            false
        );

        $this->_changelogMock = $this->getMock(
            'Magento\Framework\Mview\View\Changelog',
            array('getName'),
            array(),
            '',
            false
        );

        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $this->_objectManager->getObject(
            'Magento\Catalog\Helper\Product\Flat\Indexer',
            array(
                'context' => $contextMock,
                'resource' => $this->_resourceMock,
                'flatHelper' => $flatHelperMock,
                'eavConfig' => $eavConfigMock,
                'attributeConfig' => $attributeConfigMock,
                'configFactory' => $resourceConfigFactoryMock,
                'attributeFactory' => $eavFactoryMock,
                'storeManager' => $this->_storeManagerMock,
                'changelog' => $this->_changelogMock,
                'flatAttributeGroups' => array('catalog_product')
            )
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
            $this->returnValue(array('catalog_product_flat_1', 'catalog_product_flat_2', 'catalog_product_flat_3'))
        );

        $this->_connectionMock->expects($this->once())->method('dropTable')->with('catalog_product_flat_3');

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'write'
        )->will(
            $this->returnValue($this->_connectionMock)
        );

        $this->_setStoreManagerExpectedStores(array(1, 2));

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
                array(
                    'catalog_product_flat_1',
                    'catalog_product_flat_2',
                    'catalog_product_flat_3',
                    'catalog_product_flat_4',
                    'catalog_product_flat_cl'
                )
            )
        );

        $this->_connectionMock->expects($this->exactly(3))->method('dropTable');

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'write'
        )->will(
            $this->returnValue($this->_connectionMock)
        );

        $this->_setStoreManagerExpectedStores(array(1));

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
            $this->returnValue(array('catalog_product_flat_cl'))
        );

        $this->_connectionMock->expects($this->never())->method('dropTable');

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'write'
        )->will(
            $this->returnValue($this->_connectionMock)
        );

        $this->_setStoreManagerExpectedStores(array(1));

        $this->_model->deleteAbandonedStoreFlatTables();
    }

    /**
     * Initialize store manager mock with expected store IDs
     *
     * @param array $storeIds
     */
    protected function _setStoreManagerExpectedStores(array $storeIds)
    {
        $stores = array();
        foreach ($storeIds as $storeId) {
            $store = $this->getMock(
                'Magento\Store\Model\Store',
                array('getId', '__sleep', '__wakeup'),
                array(),
                '',
                false
            );
            $store->expects($this->once())->method('getId')->will($this->returnValue($storeId));
            $stores[] = $store;
        }

        $this->_storeManagerMock->expects($this->once())->method('getStores')->will($this->returnValue($stores));
    }
}
