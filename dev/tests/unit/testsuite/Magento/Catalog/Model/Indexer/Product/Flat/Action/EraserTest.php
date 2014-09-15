<?php
/**
 *
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

namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;


class EraserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser
     */
    protected $model;

    protected function setUp()
    {
        $resource = $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false);
        $this->connection = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');
        $resource->expects($this->any())->method('getConnection')->will($this->returnValue($this->connection));
        $this->indexerHelper = $this->getMock(
            'Magento\Catalog\Helper\Product\Flat\Indexer',
            array(),
            array(), '', false
        );
        $this->indexerHelper->expects($this->any())->method('getTable')->will($this->returnArgument(0));
        $this->indexerHelper->expects($this->any())->method('getFlatTableName')->will($this->returnValueMap(array(
            array(1, 'store_1_flat'),
            array(2, 'store_2_flat'),
        )));

        $this->storeManager = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->model = new \Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser(
            $resource,
            $this->indexerHelper,
            $this->storeManager
        );
    }

    public function testRemoveDeletedProducts()
    {
        $productsToDeleteIds = array(1, 2);
        $select = $this->getMock('\Magento\Framework\Db\Select', array(), array(), '', false);
        $select->expects($this->once())->method('from')->with('catalog_product_entity')->will($this->returnSelf());
        $select->expects($this->once())->method('where')->with('entity_id IN(?)', $productsToDeleteIds)
            ->will($this->returnSelf());
        $products = array(array('entity_id' => 2));
        $statement = $this->getMock('\Zend_Db_Statement_Interface');
        $statement->expects($this->once())->method('fetchAll')->will($this->returnValue($products));
        $this->connection->expects($this->once())->method('query')->with($select)
            ->will($this->returnValue($statement));
        $this->connection->expects($this->once())->method('select')->will($this->returnValue($select));
        $this->connection->expects($this->once())->method('delete')
            ->with('store_1_flat', array('entity_id IN(?)' => array(1)));

        $this->model->removeDeletedProducts($productsToDeleteIds, 1);
    }

    public function testDeleteProductsFromStoreForAllStores()
    {
        $store1 = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $store1->expects($this->any())->method('getId')->will($this->returnValue(1));
        $store2 = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $store2->expects($this->any())->method('getId')->will($this->returnValue(2));
        $this->storeManager->expects($this->once())->method('getStores')
            ->will($this->returnValue(array($store1, $store2)));
        $this->connection->expects($this->at(0))->method('delete')
            ->with('store_1_flat', array('entity_id IN(?)' => array(1)));
        $this->connection->expects($this->at(1))->method('delete')
            ->with('store_2_flat', array('entity_id IN(?)' => array(1)));

        $this->model->deleteProductsFromStore(1);
    }
} 
