<?php
/**
 * Creditmemo API model test.
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Sales_Model_Order_Creditmemo_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test sales order credit memo list, info, create, cancel
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/multiple_invoices.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCRUD()
    {
        $creditmemoInfo = $this->_createCreditmemo();
        list($product, $qtys, $adjustmentPositive, $adjustmentNegative, $creditMemoIncrement) = $creditmemoInfo;

        //Test list
        $creditmemoList = Magento_Test_Helper_Api::call($this, 'salesOrderCreditmemoList');
        $this->assertInternalType('array', $creditmemoList);
        $this->assertNotEmpty($creditmemoList, 'Creditmemo list is empty');

        //Test add comment
        $commentText = 'Creditmemo comment';
        $this->assertTrue(
            (bool)Magento_Test_Helper_Api::call(
                $this,
                'salesOrderCreditmemoAddComment',
                array(
                    'creditmemoIncrementId' => $creditMemoIncrement,
                    'comment' => $commentText
                )
            )
        );

        //Test info
        $creditmemoInfo = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoInfo',
            array(
                'creditmemoIncrementId' => $creditMemoIncrement
            )
        );

        $this->assertInternalType('array', $creditmemoInfo);
        $this->assertNotEmpty($creditmemoInfo);
        $this->assertEquals($creditMemoIncrement, $creditmemoInfo['increment_id']);

        //Test adjustments fees were added
        $this->assertEquals($adjustmentPositive, $creditmemoInfo['adjustment_positive']);
        $this->assertEquals($adjustmentNegative, $creditmemoInfo['adjustment_negative']);

        //Test order items were refunded
        $this->assertArrayHasKey('items', $creditmemoInfo);
        $this->assertInternalType('array', $creditmemoInfo['items']);
        $this->assertGreaterThan(0, count($creditmemoInfo['items']));

        if (!isset($creditmemoInfo['items'][0])) { // workaround for WSI plain array response
            $creditmemoInfo['items'] = array($creditmemoInfo['items']);
        }

        $this->assertEquals($creditmemoInfo['items'][0]['order_item_id'], $qtys[0]['order_item_id']);
        $this->assertEquals($product->getId(), $creditmemoInfo['items'][0]['product_id']);

        if (!isset($creditmemoInfo['comments'][0])) { // workaround for WSI plain array response
            $creditmemoInfo['comments'] = array($creditmemoInfo['comments']);
        }

        //Test comment was added correctly
        $this->assertArrayHasKey('comments', $creditmemoInfo);
        $this->assertInternalType('array', $creditmemoInfo['comments']);
        $this->assertGreaterThan(0, count($creditmemoInfo['comments']));
        $this->assertEquals($commentText, $creditmemoInfo['comments'][0]['comment']);

        //Test cancel
        //Situation when creditmemo is possible to cancel was not found
        $this->setExpectedException('SoapFault');
        Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoCancel',
            array('creditmemoIncrementId' => $creditMemoIncrement)
        );
    }

    /**
     * Test Exception when refund amount greater than available to refund amount
     *
     * @expectedException SoapFault
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/multiple_invoices.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testNegativeRefundException()
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::registry('order');
        $overRefundAmount = $order->getGrandTotal() + 10;

        Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoCreate',
            array(
                'creditmemoIncrementId' => $order->getIncrementId(),
                'creditmemoData' => (object)array(
                    'adjustment_positive' => $overRefundAmount
                )
            )
        );
    }

    /**
     * Test filtered list empty if filter contains incorrect order id
     */
    public function testListEmptyFilter()
    {
        $filter = array(
            'filter' => array((object)array('key' => 'order_id', 'value' => 'invalid-id'))
        );
        $creditmemoList = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoList',
            (object)array('filters' => $filter)
        );
        $this->assertEquals(0, count($creditmemoList));
    }

    /**
     * Test Exception on invalid creditmemo create data
     *
     * @expectedException SoapFault
     */
    public function testCreateInvalidOrderException()
    {
        Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoCreate',
            array(
                'orderIncrementId' => 'invalid-id',
                'creditmemoData' => array()
            )
        );
    }

    /**
     * Test Exception on invalid credit memo while adding comment
     *
     * @expectedException SoapFault
     */
    public function testAddCommentInvalidCreditmemoException()
    {
        Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoAddComment',
            array(
                'creditmemoIncrementId' => 'invalid-id',
                'comment' => 'Comment'
            )
        );
    }

    /**
     * Test Exception on invalid credit memo while getting info
     *
     * @expectedException SoapFault
     */
    public function testInfoInvalidCreditmemoException()
    {
        Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoInfo',
            array('creditmemoIncrementId' => 'invalid-id')
        );
    }

    /**
     * Test exception on invalid credit memo cancel
     *
     * @expectedException SoapFault
     */
    public function testCancelInvalidIdException()
    {
        Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoCancel',
            array('creditmemoIncrementId' => 'invalid-id')
        );
    }

    /**
     * Test credit memo create API call results
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/multiple_invoices.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testAutoIncrementType()
    {
        // Set creditmemo increment id prefix
        $prefix = '01';
        Magento_Test_Helper_Eav::setIncrementIdPrefix('creditmemo', $prefix);

        $order = Mage::registry('order2');

        $orderItems = $order->getAllItems();
        $qtys = array();

        /** @var $orderItem Mage_Sales_Model_Order_Item */
        foreach ($orderItems as $orderItem) {
            $qtys[] = array('order_item_id' => $orderItem->getId(), 'qty' => 1);
        }
        $adjustmentPositive = 2;
        $adjustmentNegative = 1;
        $data = array(
            'qtys' => $qtys,
            'adjustment_positive' => $adjustmentPositive,
            'adjustment_negative' => $adjustmentNegative
        );
        $orderIncrementalId = $order->getIncrementId();

        //Test create
        $creditMemoIncrement = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoCreate',
            array(
                'creditmemoIncrementId' => $orderIncrementalId,
                'creditmemoData' => $data
            )
        );
        Mage::register('creditmemoIncrementId', $creditMemoIncrement);

        $this->assertTrue(is_string($creditMemoIncrement), 'Increment Id is not a string');
        $this->assertStringStartsWith(
            $prefix,
            $creditMemoIncrement,
            'Increment Id returned by API is not correct'
        );
    }

    /**
     * Test order creditmemo list. With filters
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/multiple_invoices.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @depends testCRUD
     */
    public function testListWithFilters()
    {
        $creditmemoInfo = $this->_createCreditmemo();
        $creditMemoIncrement = end($creditmemoInfo);

        /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = Mage::getModel('Mage_Sales_Model_Order_Creditmemo')->load($creditMemoIncrement, 'increment_id');

        $filters = array(
            'filters' => (object)array(
                'filter' => array(
                    (object)array('key' => 'state', 'value' => $creditmemo->getData('state')),
                    (object)array('key' => 'created_at', 'value' => $creditmemo->getData('created_at'))
                ),
                'complex_filter' => array(
                    (object)array(
                        'key' => 'creditmemo_id',
                        'value' => (object)array('key' => 'in', 'value' => array($creditmemo->getId(), 0))
                    ),
                )
            )
        );

        $result = Magento_Test_Helper_Api::call($this, 'salesOrderCreditmemoList', $filters);

        if (!isset($result[0])) { // workaround for WS-I
            $result = array($result);
        }
        $this->assertInternalType('array', $result, "Response has invalid format");
        $this->assertEquals(1, count($result), "Invalid creditmemos quantity received");
        foreach (reset($result) as $field => $value) {
            if ($field == 'creditmemo_id') {
                // process field mapping
                $field = 'entity_id';
            }
            $this->assertEquals($creditmemo->getData($field), $value, "Field '{$field}' has invalid value");
        }
    }

    /**
     * Create creditmemo using API. Invoice fixture must be initialized for this method
     *
     * @return array
     */
    protected function _createCreditmemo()
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::registry('product_virtual');

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::registry('order');

        $orderItems = $order->getAllItems();
        $qtys = array();

        /** @var $orderItem Mage_Sales_Model_Order_Item */
        foreach ($orderItems as $orderItem) {
            $qtys[] = array('order_item_id' => $orderItem->getId(), 'qty' => 1);
        }

        $adjustmentPositive = 2;
        $adjustmentNegative = 3;
        $data = array(
            'qtys' => $qtys,
            'adjustment_positive' => $adjustmentPositive,
            'adjustment_negative' => $adjustmentNegative
        );
        $orderIncrementalId = $order->getIncrementId();

        //Test create
        $creditMemoIncrement = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderCreditmemoCreate',
            array(
                'creditmemoIncrementId' => $orderIncrementalId,
                'creditmemoData' => (object)$data
            )
        );

        /** Add creditmemo to fixtures to ensure that it is removed in teardown. */
        /** @var Mage_Sales_Model_Order_Creditmemo $createdCreditmemo */
        $createdCreditmemo = Mage::getModel('Mage_Sales_Model_Order_Creditmemo');
        $createdCreditmemo->load($creditMemoIncrement, 'increment_id');
        Mage::register('creditmemo', $createdCreditmemo);

        $this->assertNotEmpty($creditMemoIncrement, 'Creditmemo was not created');
        return array($product, $qtys, $adjustmentPositive, $adjustmentNegative, $creditMemoIncrement);
    }
}
