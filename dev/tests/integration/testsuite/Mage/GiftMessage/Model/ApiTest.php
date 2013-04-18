<?php
/**
 * Test case for gift message assigning to the shopping cart via API.
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
class Mage_GiftMessage_Model_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test setting gift message fot the whole shopping cart.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSetForQuote()
    {
        /** Prepare data. */
        $quoteId = $this->_getQuote()->getId();

        /** Call tested method. */
        $status = Magento_Test_Helper_Api::call(
            $this,
            'giftMessageSetForQuote',
            array($quoteId, $this->_getGiftMessageData())
        );
        $expectedStatus = array('entityId' => $quoteId, 'result' => true, 'error' => '');
        $this->assertEquals($expectedStatus, (array)$status, 'Gift message was not added to the quote.');

        /** Ensure that messages were actually added and saved to DB. */
        /** @var Mage_Sales_Model_Quote $updatedQuote */
        $updatedQuote = Mage::getModel('Mage_Sales_Model_Quote')->load($quoteId);
        $this->assertGreaterThan(0, (int)$updatedQuote->getGiftMessageId(), "Gift message was not added.");
        $this->_checkCreatedGiftMessage($updatedQuote->getGiftMessageId(), $this->_getGiftMessageData());
    }

    /**
     * Test setting gift message fot the specific quote item.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSetForQuoteItem()
    {
        /** Prepare data. */
        $quoteItems = $this->_getQuote()->getAllItems();
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = reset($quoteItems);

        /** Call tested method. */
        $status = Magento_Test_Helper_Api::call(
            $this,
            'giftMessageSetForQuoteItem',
            array($quoteItem->getId(), $this->_getGiftMessageData())
        );
        $expectedStatus = array('entityId' => $quoteItem->getId(), 'result' => true, 'error' => '');
        $this->assertEquals($expectedStatus, (array)$status, 'Gift message was not added to the quote.');

        /** Ensure that messages were actually added and saved to DB. */
        /** @var Mage_Sales_Model_Quote_Item $updatedQuoteItem */
        $updatedQuoteItem = Mage::getModel('Mage_Sales_Model_Quote_Item')->load($quoteItem->getId());
        $this->assertGreaterThan(0, (int)$updatedQuoteItem->getGiftMessageId(), "Gift message was not added.");
        $this->_checkCreatedGiftMessage($updatedQuoteItem->getGiftMessageId(), $this->_getGiftMessageData());

    }

    /**
     * Test setting gift message fot the specified products in shopping cart.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSetForQuoteProduct()
    {
        /** Prepare data. */
        $quoteItems = $this->_getQuote()->getAllItems();
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = reset($quoteItems);

        /** Call tested method. */
        $status = Magento_Test_Helper_Api::call(
            $this,
            'giftMessageSetForQuoteProduct',
            array(
                $this->_getQuote()->getId(),
                array(
                    (object)array(
                        'product' => (object)array('product_id' => $quoteItem->getProductId()),
                        'message' => $this->_getGiftMessageData()
                    )
                )
            )
        );
        $expectedStatus = array((object)array('entityId' => $quoteItem->getId(), 'result' => true, 'error' => ''));
        $this->assertEquals($expectedStatus, $status, 'Gift message was not added to the quote.');

        /** Ensure that messages were actually added and saved to DB. */
        /** @var Mage_Sales_Model_Quote_Item $updatedQuoteItem */
        $updatedQuoteItem = Mage::getModel('Mage_Sales_Model_Quote_Item')->load($quoteItem->getId());
        $this->assertGreaterThan(0, (int)$updatedQuoteItem->getGiftMessageId(), "Gift message was not added.");
        $this->_checkCreatedGiftMessage($updatedQuoteItem->getGiftMessageId(), $this->_getGiftMessageData());
    }

    /**
     * Prepare gift message data for tests.
     *
     * @return object
     */
    protected function _getGiftMessageData()
    {
        $giftMessageData = (object)array(
            'from' => 'from@null.null',
            'to' => 'to@null.null',
            'message' => 'Gift message content.'
        );
        return $giftMessageData;
    }

    /**
     * Ensure that added gift message was successfully stored in DB.
     *
     * @param int $giftMessageId
     * @param object $giftMessageData
     */
    protected function _checkCreatedGiftMessage($giftMessageId, $giftMessageData)
    {
        $giftMessage = Mage::getModel('Mage_GiftMessage_Model_Message')->load($giftMessageId);
        $this->assertEquals($giftMessageData->message, $giftMessage['message'], 'Message stored in DB is invalid.');
        $this->assertEquals($giftMessageData->to, $giftMessage['recipient'], 'Recipient stored in DB is invalid.');
        $this->assertEquals($giftMessageData->from, $giftMessage['sender'], 'Sender stored in DB is invalid.');
    }

    /**
     * Retrieve quote created in fixture.
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        /** @var $session Mage_Checkout_Model_Session */
        $session = Mage::getModel('Mage_Checkout_Model_Session');
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $session->getQuote();
        return $quote;
    }
}
