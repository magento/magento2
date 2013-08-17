<?php
/**
 * Checkout API tests.
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
 * @magentoDbIsolation enabled
 */
class Mage_Checkout_Model_Cart_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for product add to shopping cart.
     *
     * @magentoDataFixture Mage/Catalog/_files/product_simple_duplicated.php
     * @magentoDataFixture Mage/Checkout/_files/quote.php
     */
    public function testProductAddToCart()
    {
        /** @var Mage_Sales_Model_Resource_Quote_Collection $quoteCollection */
        $quoteCollection = Mage::getModel('Mage_Sales_Model_Resource_Quote_Collection');
        $quote = $quoteCollection->getFirstItem();
        $productSku = 'simple-1';

        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartProductAdd',
            array(
                'quoteId' => $quote->getId(),
                'productsData' => array(
                    (object)array('sku' => $productSku, 'qty' => 1)
                )
            )
        );

        $this->assertTrue($soapResult, 'Error during product add to cart via API call');
    }

    /**
     * Negative test for adding a non-existing product to shopping cart.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote.php
     */
    public function testProductAddToCartWithNonExistingProduct()
    {
        /** @var Mage_Sales_Model_Resource_Quote_Collection $quoteCollection */
        $quoteCollection = Mage::getModel('Mage_Sales_Model_Resource_Quote_Collection');
        $quote = $quoteCollection->getFirstItem();
        $productId = "0";

        $errorCode = 1033;
        $errorMessage = 'Product does not exist.';
        $exception = Magento_Test_Helper_Api::callWithException(
            $this,
            'shoppingCartProductAdd',
            array(
                'quoteId' => $quote->getId(),
                'productsData' => array(
                    (object)array('product_id' => $productId, 'qty' => 1)
                )
            )
        );
        $this->_assertError($errorCode, $errorMessage, $exception->faultcode, $exception->faultstring);
    }

    /**
     * Test for product with custom options add to shopping cart.
     *
     * @magentoDataFixture Mage/Catalog/_files/product_with_options.php
     * @magentoDataFixture Mage/Checkout/_files/quote.php
     */
    public function testProductWithCustomOptionsAddToCart()
    {
        // Create custom option for product
        $customOptionId = null;
        $customOptionTitle = 'test_option_code_1';
        $customOptionValue = 'option_value';
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load(1);
        /** @var Mage_Sales_Model_Resource_Quote_Collection $quoteCollection */
        $quoteCollection = Mage::getModel('Mage_Sales_Model_Resource_Quote_Collection');
        $quote = $quoteCollection->getFirstItem();

        // Find ID of created custom option for future use
        /** @var $productOption Mage_Catalog_Model_Product_Option */
        $productOption = Mage::getModel('Mage_Catalog_Model_Product_Option');

        foreach ($productOption->getProductOptionCollection($product) as $option) {
            if ($option['default_title'] == $customOptionTitle) {
                $customOptionId = $option['option_id'];
                break;
            }
        }
        if (null === $customOptionId) {
            $this->fail('Can not find custom option ID that been created');
        }

        $customOptionsData = array($customOptionId => $customOptionValue);
        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartProductAdd',
            array(
                'quoteId' => $quote->getId(),
                'productsData' => array(
                    (object)array('sku' => $product->getSku(), 'qty' => 1, 'options' => $customOptionsData)
                )
            )
        );

        $this->assertTrue($soapResult, 'Error during product with custom options add to cart via API call');

        /** @var $quoteItemOption Mage_Sales_Model_Resource_Quote_Item_Option_Collection */
        $quoteItemOption = Mage::getResourceModel('Mage_Sales_Model_Resource_Quote_Item_Option_Collection');
        $itemOptionValue = null;

        foreach ($quoteItemOption->getOptionsByProduct($product) as $row) {
            if ('option_' . $customOptionId == $row['code']) {
                $itemOptionValue = $row['value'];
                break;
            }
        }
        if (null === $itemOptionValue) {
            $this->fail('Custom option value not found in DB after API call');
        }
        $this->assertEquals(
            $customOptionValue,
            $itemOptionValue,
            'Custom option value in DB does not match value passed by API'
        );
    }

    /**
     * Test for product list from shopping cart API method.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product.php
     */
    public function testCartProductList()
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load(1);
        /** @var Mage_Sales_Model_Resource_Quote_Collection $quoteCollection */
        $quoteCollection = Mage::getModel('Mage_Sales_Model_Resource_Quote_Collection');
        $quote = $quoteCollection->getFirstItem();

        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartProductList',
            array('quoteId' => $quote->getId())
        );

        $this->assertInternalType('array', $soapResult, 'Product List call result is not an array');

        if (0 === key($soapResult)) {
            $this->assertCount(1, $soapResult, 'Product List call result contain not exactly one product');

            $soapResult = $soapResult[0]; //workaround for different result structure
        }
        $this->assertArrayHasKey('sku', $soapResult, 'Product List call result does not contain a product sku');
        $this->assertEquals($product->getSku(), $soapResult['sku'], 'Product Sku does not match fixture');
    }

    /**
     * Test for product list from shopping cart API method
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_check_payment.php
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testCreateOrder()
    {
        // Set order increment id prefix
        $prefix = '01';
        Magento_Test_Helper_Eav::setIncrementIdPrefix('order', $prefix);

        $quote = $this->_getQuoteFixture();
        $orderIncrementId = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartOrder',
            array(
                'quoteId' => $quote->getId()
            )
        );

        $this->assertTrue(is_string($orderIncrementId), 'Increment Id is not a string');
        $this->assertStringStartsWith($prefix, $orderIncrementId, 'Increment Id returned by API is not correct');
    }

    /**
     * Test order creation with payment method
     *
     * @magentoConfigFixture current_store payment/ccsave/active 1
     * @magentoConfigFixture current_store carriers/flatrate/active 1
     * @magentoDataFixture Mage/Checkout/_files/quote_with_ccsave_payment.php
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testCreateOrderWithPayment()
    {
        $quote = $this->_getQuoteFixture();
        $paymentMethod = array(
            'method' => 'ccsave',
            'cc_owner' => 'user',
            'cc_type' => 'VI',
            'cc_exp_month' => 5,
            'cc_exp_year' => 2016,
            'cc_number' => '4584728193062819',
            'cc_cid' => '000',
        );

        $orderIncrementId = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartOrderWithPayment',
            array(
                'quoteId' => $quote->getId(),
                'store' => null,
                'agreements' => null,
                'paymentData' => (object)$paymentMethod
            )
        );

        $this->assertTrue(is_string($orderIncrementId), 'Increment Id is not a string');
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($orderIncrementId);
        $this->assertEquals('ccsave', $order->getPayment()->getMethod());
    }

    /**
     * Test order creation with not available payment method
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_ccsave_payment.php
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderWithNotAvailablePayment()
    {
        $quote = $this->_getQuoteFixture();
        $paymentMethod = array(
            'method' => 'paypal_direct',
            'cc_owner' => 'user',
            'cc_type' => 'VI',
            'cc_exp_month' => 5,
            'cc_exp_year' => 2016,
            'cc_number' => '4584728193062819',
            'cc_cid' => '000',
        );

        $errorCode = 1075;
        $errorMessage = 'The requested Payment Method is not available.';

        $exception = Magento_Test_Helper_Api::callWithException(
            $this,
            'shoppingCartOrderWithPayment',
            array(
                'quoteId' => $quote->getId(),
                'store' => null,
                'agreements' => null,
                'paymentData' => (object)$paymentMethod
            )
        );
        $this->_assertError($errorCode, $errorMessage, $exception->faultcode, $exception->faultstring);
    }

    /**
     * Test order creation with payment method data empty
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_ccsave_payment.php
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderWithEmptyPaymentData()
    {
        $quote = $this->_getQuoteFixture();
        $errorCode = 1071;
        $errorMessage = 'Payment method data is empty.';

        $exception = Magento_Test_Helper_Api::callWithException(
            $this,
            'shoppingCartOrderWithPayment',
            array(
                'quoteId' => $quote->getId(),
                'store' => null,
                'agreements' => null,
                'paymentData' => array()
            )
        );
        $this->_assertError($errorCode, $errorMessage, $exception->faultcode, $exception->faultstring);
    }

    /**
     * Test order creation with invalid payment method data
     *
     * @magentoConfigFixture current_store payment/ccsave/active 1
     * @magentoDataFixture Mage/Checkout/_files/quote_with_ccsave_payment.php
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderWithInvalidPaymentData()
    {
        $quote = $this->_getQuoteFixture();
        $paymentMethod = array(
            'method' => 'ccsave',
            'cc_owner' => 'user',
            'cc_type' => 'VI',
            'cc_exp_month' => 5,
            'cc_exp_year' => 2010,
            'cc_number' => '4584728193062819',
            'cc_cid' => '000',
        );
        $errorCode = 1075;
        $errorMessage = 'We found an incorrect credit card expiration date.';
        $exception = Magento_Test_Helper_Api::callWithException(
            $this,
            'shoppingCartOrderWithPayment',
            array(
                'quoteId' => $quote->getId(),
                'store' => null,
                'agreements' => null,
                'paymentData' => (object)$paymentMethod
            )
        );
        $this->_assertError($errorCode, $errorMessage, $exception->faultcode, $exception->faultstring);
    }

    /**
     * Assert that error code and message equals expected
     *
     * @param int $expectedCode
     * @param string $expectedMessage
     * @param int $actualCode
     * @param string $actualMessage
     */
    protected function _assertError($expectedCode, $expectedMessage, $actualCode, $actualMessage)
    {
        $this->assertEquals($expectedCode, $actualCode);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    /**
     * Test info method.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_check_payment.php
     */
    public function testInfo()
    {
        /** @var Mage_Checkout_Model_Cart $quote */
        $quote = $this->_getQuoteFixture();
        $quoteId = $quote->getId();
        /** Retrieve quote info. */
        $quoteInfo = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartInfo',
            array($quoteId)
        );
        /** Assert quote info retrieving was successful. */
        $this->assertNotEmpty($quoteInfo, 'Quote info retrieving was unsuccessful.');
        /** Assert base fields are present in the response. */
        $expectedFields = array('shipping_address', 'billing_address', 'items', 'payment');
        $missingFields = array_diff($expectedFields, array_keys($quoteInfo));
        $this->assertEmpty(
            $missingFields,
            sprintf("The following fields must be present in response: %s.", implode(', ', $missingFields))
        );
        /** Assert retrieved quote id is correct. */
        $this->assertEquals($quoteId, $quoteInfo['quote_id'], 'Quote Id retrieving was unsuccessful.');
    }

    /**
     * Test totals method.
     *
     * @magentoDataFixture Mage/Checkout/_files/quote_with_check_payment.php
     */
    public function testTotals()
    {
        if (Magento_Test_Helper_Bootstrap::getInstance()->getDbVendorName() != 'mysql') {
            $this->markTestSkipped('Legacy API is expected to support MySQL only.');
        }
        /** @var Mage_Checkout_Model_Cart $quote */
        $quote = $this->_getQuoteFixture();
        $quoteId = $quote->getId();
        /** Retrieve quote info. */
        $quoteTotals = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartTotals',
            array($quoteId)
        );
        /** Assert quote totals retrieving were successful. */
        $this->assertNotEmpty($quoteTotals, 'Quote totals retrieving were unsuccessful.');
        /** Assert totals titles. */
        $expectedQuotesTitles = array('Subtotal', 'Grand Total');
        $actualQuotesTitles = array();
        $grandTotal = null;
        foreach ($quoteTotals as $quoteTotal) {
            $actualQuotesTitles[] = $quoteTotal['title'];
            if ($quoteTotal['title'] == 'Grand Total') {
                $grandTotal = $quoteTotal;
            }
        }
        $missingQuotesTitles = array_diff($expectedQuotesTitles, $actualQuotesTitles);
        $this->assertEmpty(
            $missingQuotesTitles,
            sprintf("The following quotes titles must be present in response: %s.", implode(', ', $missingQuotesTitles))
        );
        /** Assert grand total is retrieved correct. */
        $expectedGrandTotal = array('title' => 'Grand Total', 'amount' => 20);
        $this->assertEquals($expectedGrandTotal, $grandTotal, 'Grand total retrieving was unsuccessful.');
    }

    /**
     * Test licenseAgreement method.
     *
     * @magentoConfigFixture current_store checkout/options/enable_agreements 1
     * @magentoDataFixture Mage/Checkout/Model/Cart/Api/_files/license_agreement.php
     * @magentoDataFixture Mage/Checkout/_files/quote_with_check_payment.php
     */
    public function testLicenseAgreement()
    {
        /** @var Mage_Checkout_Model_Cart $quote */
        $quote = $this->_getQuoteFixture();
        $quoteId = $quote->getId();
        /** Retrieve quote license agreement. */
        $licenseAgreement = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartLicense',
            array($quoteId)
        );
        /** Assert quote license agreement retrieving were successful. */
        $this->assertNotEmpty($licenseAgreement, 'Quote license agreement retrieving was unsuccessful.');
        /** Assert license info is retrieved correct. */
        /** @var Mage_Checkout_Model_Agreement $agreement */
        $agreement = Mage::getModel('Mage_Checkout_Model_Agreement')->load('Agreement name', 'name');
        $agreementData = $agreement->getData();
        unset($agreementData['store_id']);
        $this->assertEquals($agreementData, reset($licenseAgreement), 'License agreement data is incorrect.');
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
