<?php
/**
 * Tests for shipping method in shopping cart API.
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
 * @magentoDataFixture Mage/Checkout/_files/quote_with_check_payment.php
 */
class Mage_Checkout_Model_Cart_Shipping_ApiTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        /** Collect rates before requesting them via API. */
        $this->_getQuoteFixture()->getShippingAddress()->setCollectShippingRates(true)->collectTotals()->save();
        parent::setUp();
    }

    /**
     * Test retrieving of shipping methods applicable to the shopping cart.
     *
     */
    public function testGetShippingMethodsList()
    {
        /** Retrieve the list of available shipping methods via API. */
        $shippingMethodsList = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartShippingList',
            array(
                $this->_getQuoteFixture()->getId()
            )
        );

        /** Verify the API call results. */
        $this->assertCount(1, $shippingMethodsList, 'There is exactly 1 shipping method expected.');
        $expectedItemData = array(
            'code' => 'flatrate_flatrate',
            'carrier' => 'flatrate',
            'carrier_title' => 'Flat Rate',
            'method' => 'flatrate',
            'method_title' => 'Fixed',
            'method_description' => null,
            'price' => 10
        );
        Magento_Test_Helper_Api::checkEntityFields($this, $expectedItemData, reset($shippingMethodsList));
    }

    /**
     * Test assigning shipping method to quote.
     *
     * @magentoDbIsolation enabled
     */
    public function testSetShippingMethod()
    {
        /** Prepare data. */
        $this->_getQuoteFixture()->getShippingAddress()->setShippingMethod(null)->save();
        /** @var Mage_Sales_Model_Quote $quoteBefore */
        $quoteBefore = Mage::getModel('Mage_Sales_Model_Quote')->load($this->_getQuoteFixture()->getId());
        $this->assertNull(
            $quoteBefore->getShippingAddress()->getShippingMethod(),
            "There should be no shipping method assigned to quote before assigning via API."
        );

        /** Retrieve the list of available shipping methods via API. */
        $shippingMethod = 'flatrate_flatrate';
        $isAdded = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartShippingMethod',
            array(
                $this->_getQuoteFixture()->getId(),
                $shippingMethod
            )
        );
        $this->assertTrue($isAdded, "Shipping method was not assigned to the quote.");

        /** Ensure that data was saved to DB. */
        /** @var Mage_Sales_Model_Quote $quoteAfter */
        $quoteAfter = Mage::getModel('Mage_Sales_Model_Quote')->load($this->_getQuoteFixture()->getId());
        $this->assertEquals(
            $shippingMethod,
            $quoteAfter->getShippingAddress()->getShippingMethod(),
            "Shipping method was assigned to quote incorrectly."
        );
    }

    /**
     * Retrieve the quote object created in fixture.
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuoteFixture()
    {
        /** @var Mage_Sales_Model_Resource_Quote_Collection $quoteCollection */
        $quoteCollection = Mage::getModel('Mage_Sales_Model_Resource_Quote_Collection');
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $quoteCollection->getFirstItem();
        return $quote;
    }
}
