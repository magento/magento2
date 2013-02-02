<?php
/**
 * Checkout Cart Product API tests.
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
class Mage_Checkout_Model_Cart_Product_ApiTest extends Mage_Checkout_Model_Cart_AbstractTest
{
    /**
     * Test quote item update.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product.php
     */
    public function testUpdate()
    {
        $quote = $this->_getQuote();
        $quoteItems = $quote->getAllItems();
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = reset($quoteItems);
        $this->assertEquals(1, $quoteItem->getQty(), 'Quote item should have qty = 1.');

        $qtyToUpdate = 5;
        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartProductUpdate',
            array(
                'quoteId' => $quote->getId(),
                'productsData' => array(
                    (object)array(
                        'sku' => 'simple',
                        'qty' => $qtyToUpdate,
                    )
                )
            )
        );

        $this->assertTrue($soapResult, 'Error during product update in cart via API call');
        /** @var Mage_Sales_Model_Quote $quoteAfterUpdate */
        $quoteAfterUpdate = Mage::getModel('Mage_Sales_Model_Quote');
        $quoteAfterUpdate->load($quote->getId());
        $quoteItemsUpdated = $quoteAfterUpdate->getAllItems();
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItemUpdated = reset($quoteItemsUpdated);
        $this->assertEquals($qtyToUpdate, $quoteItemUpdated->getQty(), 'Incorrect quote item quantity after update.');
    }

    /**
     * Test quote item remove.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product.php
     */
    public function testRemove()
    {
        $quote = $this->_getQuote();
        $this->assertCount(1, $quote->getAllItems(), 'Quote should have exactly 1 item.');

        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartProductRemove',
            array(
                'quoteId' => $quote->getId(),
                'productsData' => array(
                    (object)array(
                        'sku' => 'simple',
                    )
                )
            )
        );

        $this->assertTrue($soapResult, 'Error during product remove from cart via API call');
        /** @var Mage_Sales_Model_Quote $quoteAfterUpdate */
        $quoteAfterUpdate = Mage::getModel('Mage_Sales_Model_Quote');
        $quoteAfterUpdate->load($quote->getId());
        $this->assertCount(0, $quoteAfterUpdate->getAllItems(), 'Quote item was not deleted.');
    }

    /**
     * Test quote item moving from inactive quote to active customer quote.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product.php
     * @magentoDataFixture Mage/Customer/_files/customer.php
     * @magentoDbIsolation enabled
     * @magentoConfigIsolation enabled
     */
    public function testMoveToCustomerQuote()
    {
        /** Prepare data. */
        $inactiveQuote = $this->_getQuote();
        $this->assertCount(1, $inactiveQuote->getAllItems(), 'Quote should have exactly 1 item.');
        $inactiveQuote->setIsActive(0)->setCustomerId(1)->save();
        $activeQuote = Mage::getModel('Mage_Sales_Model_Quote');
        $activeQuote->setData(array(
            'store_id' => 1,
            'is_active' => 1,
            'is_multi_shipping' => 0,
            'customer_id' => 1
        ));
        $activeQuote->save();

        /** Move products from inactive quote via API. */
        $isSuccessful = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartProductMoveToCustomerQuote',
            array(
                'quoteId' => $inactiveQuote->getId(),
                'productsData' => array(
                    (object)array(
                        'product_id' => '1'
                    )
                )
            )
        );
        $this->assertTrue($isSuccessful, "Product was not moved from inactive quote to active one.");

        /** Ensure that data was saved to DB correctly. */
        /** @var Mage_Sales_Model_Quote $quoteAfterMove */
        $quoteAfterMove = Mage::getModel('Mage_Sales_Model_Quote');
        $quoteAfterMove->load($activeQuote->getId());
        /** @var Mage_Sales_Model_Resource_Quote_Item_Collection $itemsCollection */
        $itemsCollection = $quoteAfterMove->getItemsCollection(false);
        $this->assertCount(1, $itemsCollection->getItems(), 'Product was not moved from inactive quote to active one.');
    }
}
