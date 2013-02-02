<?php
/**
 * Tests for invoice API.
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
class Mage_Sales_Model_Order_Invoice_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test create and read created invoice
     *
     * @magentoDataFixture Mage/Sales/_files/order.php
     * @magentoDbIsolation enabled
     */
    public function testCreate()
    {
        /** Prepare data. */
        $order = $this->_getFixtureOrder();
        $this->assertCount(
            0,
            $order->getInvoiceCollection(),
            'There must be 0 invoices before invoice creation via API.'
        );

        /** Create new invoice via API. */
        $newInvoiceId = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderInvoiceCreate',
            array(
                'orderIncrementId' => $order->getIncrementId(),
                'itemsQty' => array(),
                'comment' => 'invoice Created',
                'email' => true,
                'includeComment' => true
            )
        );
        $this->assertGreaterThan(0, (int)$newInvoiceId, 'Invoice was not created.');

        /** Ensure that invoice was created. */
        /** @var Mage_Sales_Model_Order $invoicedOrder */
        $invoicedOrder = Mage::getModel('Mage_Sales_Model_Order');
        $invoicedOrder->loadByIncrementId($order->getIncrementId());
        $invoiceCollection = $invoicedOrder->getInvoiceCollection();
        $this->assertCount(1, $invoiceCollection->getItems(), 'Invoice was not created.');
        /** @var Mage_Sales_Model_Order_Invoice $createdInvoice */
        $createdInvoice = $invoiceCollection->getFirstItem();
        $this->assertEquals(
            $createdInvoice->getIncrementId(),
            $newInvoiceId,
            'Invoice ID in call response is invalid.'
        );
    }

    /**
     * Test create and read created invoice
     *
     * @magentoDataFixture Mage/Sales/_files/invoice.php
     */
    public function testInfo()
    {
        /** Retrieve invoice data via API. */
        $invoiceData = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderInvoiceInfo',
            array(
                $this->_getFixtureInvoice()->getIncrementId(),
            )
        );

        /** Check received data validity. */
        $fieldsToCheck = array(
            'increment_id',
            'parent_id',
            'store_id',
            'order_id',
            'state',
            'entity_id' => 'invoice_id',
            'base_grand_total'
        );
        Magento_Test_Helper_Api::checkEntityFields(
            $this,
            $this->_getFixtureInvoice()->getData(),
            $invoiceData,
            $fieldsToCheck
        );
    }

    /**
     * Test adding comment to invoice via API.
     *
     * @magentoDataFixture Mage/Sales/_files/invoice.php
     * @magentoDbIsolation enabled
     */
    public function testAddComment()
    {
        /** Prepare data. */
        $commentText = "Test invoice comment.";

        /** Retrieve invoice data via API. */
        $isAdded = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderInvoiceAddComment',
            array(
                $this->_getFixtureInvoice()->getIncrementId(),
                $commentText,
                true, // send invoice via email
                true // include comment in email
            )
        );
        $this->assertTrue($isAdded, "Comment was not added to the invoice.");

        /** Verify that comment was actually added. */
        /** @var Mage_Sales_Model_Resource_Order_Invoice_Comment_Collection $commentsCollection */
        $commentsCollection = $this->_getFixtureInvoice()->getCommentsCollection(true);
        $this->assertCount(1, $commentsCollection->getItems(), "There must be exactly 1 invoice comment.");
        /** @var Mage_Sales_Model_Order_Invoice_Comment $createdComment */
        $createdComment = $commentsCollection->getFirstItem();
        $this->assertEquals($commentText, $createdComment->getComment(), 'Invoice comment text is invalid.');
    }

    /**
     * Test capturing invoice via API.
     *
     * @magentoDataFixture Mage/Sales/_files/invoice_verisign.php
     */
    public function testCapture()
    {
        /**
         * To avoid complicated environment emulation for online payment,
         * we can check if proper error message from payment gateway was received or not.
         */
        $this->setExpectedException('SoapFault', 'Invalid vendor account');

        /** Capture invoice data via API. */
        $invoiceBefore = $this->_getFixtureInvoice();
        $this->assertTrue($invoiceBefore->canCapture(), "Invoice fixture cannot be captured.");
        Magento_Test_Helper_Api::call($this, 'salesOrderInvoiceCapture', array($invoiceBefore->getIncrementId()));
    }

    /**
     * Test voiding captured invoice via API.
     *
     * @magentoDataFixture Mage/Sales/_files/invoice_verisign.php
     */
    public function testVoid()
    {
        /**
         * To avoid complicated environment emulation for online voiding,
         * we can check if proper error message from payment gateway was received or not.
         */
        $this->setExpectedException('SoapFault', 'Invalid vendor account');

        /** Prepare data. Make invoice voidable. */
        $invoiceBefore = $this->_getFixtureInvoice();
        $invoiceBefore->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID)->setCanVoidFlag(true)->save();

        /** Capture invoice data via API. */
        $this->assertTrue($invoiceBefore->canVoid(), "Invoice fixture cannot be voided.");
        Magento_Test_Helper_Api::call($this, 'salesOrderInvoiceVoid', array($invoiceBefore->getIncrementId()));
    }

    /**
     * Test cancelling invoice via API.
     *
     * @magentoDataFixture Mage/Sales/_files/invoice_verisign.php
     */
    public function testCancel()
    {
        /** Capture invoice data via API. */
        $invoiceBefore = $this->_getFixtureInvoice();
        $this->assertTrue($invoiceBefore->canCancel(), "Invoice fixture cannot be cancelled.");
        $isCanceled = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderInvoiceCancel',
            array($invoiceBefore->getIncrementId())
        );
        $this->assertTrue($isCanceled, "Invoice was not canceled successfully.");

        /** Ensure that invoice was actually cancelled. */
        $invoiceAfter = $this->_getFixtureInvoice();
        $this->assertEquals(
            Mage_Sales_Model_Order_Invoice::STATE_CANCELED,
            $invoiceAfter->getState(),
            "Invoice was not cancelled."
        );
    }

    /**
     * Retrieve invoice declared in fixture.
     *
     * This method reloads data and creates new object with each call.
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _getFixtureInvoice()
    {
        $order = $this->_getFixtureOrder();
        $invoiceCollection = $order->getInvoiceCollection();
        $this->assertCount(1, $invoiceCollection->getItems(), 'There must be exactly 1 invoice assigned to the order.');
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $invoiceCollection->getFirstItem();
        return $invoice;
    }

    /**
     * Retrieve order declared in fixture.
     *
     * This method reloads data and creates new object with each call.
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getFixtureOrder()
    {
        $orderIncrementId = '100000001';
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('Mage_Sales_Model_Order');
        $order->loadByIncrementId($orderIncrementId);
        return $order;
    }

    /**
     * Test credit memo create API call results
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/order.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testAutoIncrementType()
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $order = Mage::registry('order2');
        $incrementId = $order->getIncrementId();

        // Set invoice increment id prefix
        $prefix = '01';
        Magento_Test_Helper_Eav::setIncrementIdPrefix('invoice', $prefix);

        // Create new invoice
        $newInvoiceId = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderInvoiceCreate',
            array(
                'orderIncrementId' => $incrementId,
                'itemsQty' => array(),
                'comment' => 'invoice Created',
                'email' => true,
                'includeComment' => true
            )
        );

        $this->assertTrue(is_string($newInvoiceId), 'Increment Id is not a string');
        $this->assertStringStartsWith($prefix, $newInvoiceId, 'Increment Id returned by API is not correct');
        Mage::register('invoiceIncrementId', $newInvoiceId);
    }

    /**
     * Test order invoice list. With filters
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/multiple_invoices.php
     * @magentoAppIsolation enabled
     */
    public function testListWithFilters()
    {
        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = Mage::registry('invoice');

        $filters = array(
            'filters' => (object)array(
                'filter' => array(
                    (object)array('key' => 'state', 'value' => $invoice->getData('state')),
                    (object)array('key' => 'created_at', 'value' => $invoice->getData('created_at'))
                ),
                'complex_filter' => array(
                    (object)array(
                        'key' => 'invoice_id',
                        'value' => (object)array('key' => 'in', 'value' => array($invoice->getId(), 0))
                    ),
                )
            )
        );

        $result = Magento_Test_Helper_Api::call($this, 'salesOrderInvoiceList', $filters);

        if (!isset($result[0])) { // workaround for WS-I
            $result = array($result);
        }
        $this->assertInternalType('array', $result, "Response has invalid format");
        $this->assertEquals(1, count($result), "Invalid invoices quantity received");

        /** Reload invoice data to ensure it is up to date. */
        $invoice->load($invoice->getId());
        foreach (reset($result) as $field => $value) {
            if ($field == 'invoice_id') {
                // process field mapping
                $field = 'entity_id';
            }
            $this->assertEquals($invoice->getData($field), $value, "Field '{$field}' has invalid value");
        }
    }
}
