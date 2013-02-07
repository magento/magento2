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
 * @category    Magento
 * @package     Magento_Checkout
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Checkout_CartController
 */
class Mage_Checkout_CartControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * Test for Mage_Checkout_CartController::configureAction() with simple product
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product.php
     */
    public function testConfigureActionWithSimpleProduct()
    {
        /** @var $session Mage_Checkout_Model_Session  */
        $session = Mage::getModel('Mage_Checkout_Model_Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 1);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), Mage_Core_Model_Message::ERROR, 'Mage_Checkout_Model_Session');

        $this->assertSelectCount('button.button.btn-cart[type="button"][title="Update Cart"]', 1, $response->getBody(),
            'Response for simple product doesn\'t contain "Update Cart" button');
    }

    /**
     * Test for Mage_Checkout_CartController::configureAction() with simple product and custom option
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product_and_custom_option.php
     */
    public function testConfigureActionWithSimpleProductAndCustomOption()
    {
        /** @var $session Mage_Checkout_Model_Session  */
        $session = Mage::getModel('Mage_Checkout_Model_Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 1);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product with custom option');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), Mage_Core_Model_Message::ERROR, 'Mage_Checkout_Model_Session');

        $this->assertSelectCount('button.button.btn-cart[type="button"][title="Update Cart"]', 1, $response->getBody(),
            'Response for simple product with custom option doesn\'t contain "Update Cart" button');

        $this->assertSelectCount('input.product-custom-option[type="text"]', 1, $response->getBody(),
            'Response for simple product with custom option doesn\'t contain custom option input field');
    }

    /**
     * Test for Mage_Checkout_CartController::configureAction() with bundle product
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_bundle_product.php
     */
    public function testConfigureActionWithBundleProduct()
    {
        /** @var $session Mage_Checkout_Model_Session  */
        $session = Mage::getModel('Mage_Checkout_Model_Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 3);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for bundle product');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), Mage_Core_Model_Message::ERROR, 'Mage_Checkout_Model_Session');

        $this->assertSelectCount('button.button.btn-cart[type="button"][title="Update Cart"]', 1, $response->getBody(),
            'Response for bundle product doesn\'t contain "Update Cart" button');
    }

    /**
     * Test for Mage_Checkout_CartController::configureAction() with downloadable product
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_downloadable_product.php
     */
    public function testConfigureActionWithDownloadableProduct()
    {
        /** @var $session Mage_Checkout_Model_Session  */
        $session = Mage::getModel('Mage_Checkout_Model_Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 1);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for downloadable product');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), Mage_Core_Model_Message::ERROR, 'Mage_Checkout_Model_Session');

        $this->assertSelectCount('button.button.btn-cart[type="button"][title="Update Cart"]', 1, $response->getBody(),
            'Response for downloadable product doesn\'t contain "Update Cart" button');

        $this->assertSelectCount('ul#downloadable-links-list.options-list', 1, $response->getBody(),
            'Response for downloadable product doesn\'t contain links for download');
    }

    /**
     * Test for Mage_Checkout_CartController::configureAction() with configurable product
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_configurable_product.php
     */
    public function testConfigureActionWithConfigurableProduct()
    {
        /** @var $session Mage_Checkout_Model_Session  */
        $session = Mage::getModel('Mage_Checkout_Model_Session');

        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), 1);
        $this->assertNotNull($quoteItem, 'Cannot get quote item for configurable product');

        $this->dispatch('checkout/cart/configure/id/' . $quoteItem->getId());
        $response = $this->getResponse();

        $this->assertSessionMessages($this->isEmpty(), Mage_Core_Model_Message::ERROR, 'Mage_Checkout_Model_Session');

        $this->assertSelectCount('button.button.btn-cart[type="button"][title="Update Cart"]', 1, $response->getBody(),
            'Response for configurable product doesn\'t contain "Update Cart" button');

        $this->assertSelectCount('select.super-attribute-select', 1, $response->getBody(),
            'Response for configurable product doesn\'t contain select for super attribute');
    }

    /**
     * Gets Mage_Sales_Model_Quote_Item from Mage_Sales_Model_Quote by product id
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param $productId
     * @return Mage_Sales_Model_Quote_Item|null
     */
    private function _getQuoteItemIdByProductId($quote, $productId)
    {
        /** @var $quoteItems Mage_Sales_Model_Quote_Item[] */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($productId == $quoteItem->getProductId()) {
                return $quoteItem;
            }
        }
        return null;
    }
}
