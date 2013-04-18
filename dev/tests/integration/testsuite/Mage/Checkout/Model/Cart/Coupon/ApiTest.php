<?php
/**
 * Coupon API tests.
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
 * @magentoDataFixture Mage/Checkout/_files/quote_with_simple_product.php
 * @magentoDataFixture Mage/Checkout/_files/discount_10percent.php
 */
class Mage_Checkout_Model_Cart_Coupon_ApiTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Catalog_Model_Product */
    protected $_product;
    /** @var Mage_Sales_Model_Quote */
    protected $_quote;
    /** @var Mage_SalesRule_Model_Rule */
    protected $_salesRule;

    /**
     * We can't put this code inside setUp() as it will be called before fixtures execution
     */
    protected function _init()
    {
        $this->_product = Mage::getModel('Mage_Catalog_Model_Product')->load(1);
        $this->_quote = Mage::getModel('Mage_Sales_Model_Resource_Quote_Collection')->getFirstItem();
        $this->_salesRule = Mage::getModel('Mage_SalesRule_Model_Rule')->load('Test Coupon', 'name');
    }

    /**
     * Test coupon code applying.
     */
    public function testCartCouponAdd()
    {
        $this->_init();

        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartCouponAdd',
            array(
                'quoteId' => $this->_quote->getId(),
                'couponCode' => $this->_salesRule->getCouponCode()
            )
        );

        $this->assertTrue($soapResult, 'Coupon code was not applied');
        /** @var $discountedQuote Mage_Sales_Model_Quote */
        $discountedQuote = $this->_quote->load($this->_quote->getId());
        $discountedPrice = sprintf('%01.2f', $this->_product->getPrice() * (1 - 10 / 100));

        $this->assertEquals(
            $discountedQuote->getSubtotalWithDiscount(),
            $discountedPrice,
            'Quote subtotal price does not match discounted item price'
        );
    }

    /**
     * Test coupon code removing
     */
    public function testCartCouponRemove()
    {
        $this->_init();
        $originalPrice = $this->_product->getPrice();

        // Apply coupon
        $this->_quote->setCouponCode($this->_salesRule->getCouponCode())
            ->collectTotals()
            ->save();

        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'shoppingCartCouponRemove',
            array('quoteId' => $this->_quote->getId())
        );

        $this->assertTrue($soapResult, 'Coupon code was not removed');

        /** @var $quoteWithoutDiscount Mage_Sales_Model_Quote */
        $quoteWithoutDiscount = $this->_quote->load($this->_quote->getId());

        $this->assertEquals(
            $originalPrice,
            $quoteWithoutDiscount->getSubtotalWithDiscount(),
            'Quote subtotal price does not match its original price after discount removal'
        );
    }
}
