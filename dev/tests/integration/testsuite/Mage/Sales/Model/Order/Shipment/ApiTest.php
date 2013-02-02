<?php
/**
 * Tests for shipment API.
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
class Mage_Sales_Model_Order_Shipment_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test retrieving the list of shipments related to the order via API.
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/shipment.php
     */
    public function testItems()
    {
        /** Prepare data. */
        $shipmentFixture = $this->_getShipmentFixture();
        $filters = array(
            'filters' => (object)array(
                'filter' => array(
                    (object)array('key' => 'increment_id', 'value' => $shipmentFixture->getIncrementId()),
                )
            )
        );

        /** Retrieve list of shipments via API. */
        $shipmentsList = Magento_Test_Helper_Api::call($this, 'salesOrderShipmentList', $filters);

        /** Verify received list of shipments. */
        $this->assertCount(1, $shipmentsList, "Exactly 1 shipment is expected to be in the list results.");
        $fieldsToCompare = array('increment_id', 'total_qty', 'entity_id' => 'shipment_id');
        Magento_Test_Helper_Api::checkEntityFields(
            $this,
            $shipmentFixture->getData(),
            reset($shipmentsList),
            $fieldsToCompare
        );
    }

    /**
     * Test retrieving available carriers for the specified order.
     *
     * @magentoDataFixture Mage/Sales/_files/order.php
     */
    public function testGetCarriers()
    {
        /** Prepare data. */
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('Mage_Sales_Model_Order');
        $order->loadByIncrementId('100000001');

        /** Retrieve carriers list */
        $carriersList = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderShipmentGetCarriers',
            array($order->getIncrementId())
        );

        /** Verify carriers list. */
        $this->assertCount(6, $carriersList, "Carriers list contains unexpected quantity of items.");
        $dhlCarrierData = end($carriersList);
        $expectedDhlData = array('key' => 'dhlint', 'value' => 'DHL');
        $this->assertEquals($expectedDhlData, $dhlCarrierData, "Carriers list item is invalid.");
    }

    /**
     * Test adding comment to shipment via API.
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/shipment.php
     */
    public function testAddComment()
    {
        if (Magento_Test_Bootstrap::getInstance()->getDbVendorName() != 'mysql') {
            $this->markTestIncomplete('Legacy API is expected to support MySQL only.');
        }
        /** Add comment to shipment via API. */
        $commentText = 'Shipment test comment.';
        $isAdded = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderShipmentAddComment',
            array(
                $this->_getShipmentFixture()->getIncrementId(),
                $commentText,
                true, // should email be sent?
                true, // should comment be included into email body?
            )
        );
        $this->assertTrue($isAdded, "Comment was not added to the shipment.");

        /** Ensure that comment was actually added to the shipment. */
        /** @var Mage_Sales_Model_Resource_Order_Shipment_Comment_Collection $commentsCollection */
        $commentsCollection = $this->_getShipmentFixture()->getCommentsCollection(true);
        $this->assertCount(1, $commentsCollection->getItems(), "Exactly 1 shipment comment is expected to exist.");
        /** @var Mage_Sales_Model_Order_Shipment_Comment $comment */
        $comment = $commentsCollection->getFirstItem();
        $this->assertEquals($commentText, $comment->getComment(), 'Comment text was saved to DB incorrectly.');
    }

    /**
     * Test adding and removing tracking information via shipment API.
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/shipment.php
     */
    public function testTrackOperations()
    {
        if (Magento_Test_Bootstrap::getInstance()->getDbVendorName() != 'mysql') {
            $this->markTestIncomplete('Legacy API is expected to support MySQL only.');
        }
        /** Prepare data. */
        $carrierCode = 'ups';
        $trackingTitle = 'Tracking title';
        $trackingNumber = 'N123456';

        /** Add tracking information via API. */
        $trackingNumberId = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderShipmentAddTrack',
            array($this->_getShipmentFixture()->getIncrementId(), $carrierCode, $trackingTitle, $trackingNumber)
        );
        $this->assertGreaterThan(0, (int)$trackingNumberId, "Tracking information was not added.");

        /** Ensure that tracking data was saved correctly. */
        $tracksCollection = $this->_getShipmentFixture()->getTracksCollection();
        $this->assertCount(1, $tracksCollection->getItems(), "Tracking information was not saved to DB.");
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        $track = $tracksCollection->getFirstItem();
        $this->assertEquals(
            array($carrierCode, $trackingTitle, $trackingNumber),
            array($track->getCarrierCode(), $track->getTitle(), $track->getNumber()),
            'Tracking data was saved incorrectly.'
        );

        /** Remove tracking information via API. */
        $isRemoved = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderShipmentRemoveTrack',
            array($this->_getShipmentFixture()->getIncrementId(), $trackingNumberId)
        );
        $this->assertTrue($isRemoved, "Tracking information was not removed.");

        /** Ensure that tracking data was saved correctly. */
        /** @var Mage_Sales_Model_Order_Shipment $updatedShipment */
        $updatedShipment = Mage::getModel('Mage_Sales_Model_Order_Shipment');
        $updatedShipment->load($this->_getShipmentFixture()->getId());
        $tracksCollection = $updatedShipment->getTracksCollection();
        $this->assertCount(0, $tracksCollection->getItems(), "Tracking information was not removed from DB.");
    }

    /**
     * Test shipment create and info via API.
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/order_with_shipping.php
     * @magentoDbIsolation enabled
     */
    public function testCRUD()
    {
        // Create new shipment
        $newShipmentId = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderShipmentCreate',
            array(
                'orderIncrementId' => $this->_getOrderFixture()->getIncrementId(),
                'itemsQty' => array(),
                'comment' => 'Shipment Created',
                'email' => true,
                'includeComment' => true
            )
        );
        Mage::register('shipmentIncrementId', $newShipmentId);

        // View new shipment
        $shipment = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderShipmentInfo',
            array(
                'shipmentIncrementId' => $newShipmentId
            )
        );

        $this->assertEquals($newShipmentId, $shipment['increment_id']);
    }

    /**
     * Test shipment create API.
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/order_with_shipping.php
     * @magentoDbIsolation enabled
     */
    public function testAutoIncrementType()
    {
        // Set shipping increment id prefix
        $prefix = '01';
        Magento_Test_Helper_Eav::setIncrementIdPrefix('shipment', $prefix);

        // Create new shipment
        $newShipmentId = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderShipmentCreate',
            array(
                'orderIncrementId' => $this->_getOrderFixture()->getIncrementId(),
                'itemsQty' => array(),
                'comment' => 'Shipment Created',
                'email' => true,
                'includeComment' => true
            )
        );
        Mage::unregister('shipmentIncrementId');
        Mage::register('shipmentIncrementId', $newShipmentId);

        $this->assertTrue(is_string($newShipmentId), 'Increment Id is not a string');
        $this->assertStringStartsWith($prefix, $newShipmentId, 'Increment Id returned by API is not correct');
    }

    /**
     * Test send shipping info API
     *
     * @magentoDataFixture Mage/Sales/Model/Order/Api/_files/shipment.php
     * @magentoDbIsolation enabled
     */
    public function testSendInfo()
    {
        if (Magento_Test_Bootstrap::getInstance()->getDbVendorName() != 'mysql') {
            $this->markTestIncomplete('Legacy API is expected to support MySQL only.');
        }
        $isSent = Magento_Test_Helper_Api::call(
            $this,
            'salesOrderShipmentSendInfo',
            array(
                'shipmentIncrementId' => $this->_getShipmentFixture()->getIncrementId(),
                'comment' => 'Comment text.'
            )
        );

        $this->assertTrue((bool)$isSent);
    }

    /**
     * Retrieve order from fixture.
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrderFixture()
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::registry('order');
        return $order;
    }

    /**
     * Retrieve shipment from fixture.
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _getShipmentFixture()
    {
        /** @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = Mage::registry('shipment');
        return $shipment;
    }
}
