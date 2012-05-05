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
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Model_Product_Attribute_Backend_Price.
 *
 * @magentoDataFixture Mage/Catalog/_files/product_simple.php
 */
class Mage_Catalog_Model_Product_Attribute_Backend_PriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Attribute_Backend_Price
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product_Attribute_Backend_Price;
        $this->_model->setAttribute(
            Mage::getSingleton('Mage_Eav_Model_Config')->getAttribute('catalog_product', 'price')
        );
    }

    public function testSetScopeDefault()
    {
        /* validate result of setAttribute */
        $this->assertEquals(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            $this->_model->getAttribute()->getIsGlobal()
        );
        $this->_model->setScope($this->_model->getAttribute());
        $this->assertEquals(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            $this->_model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testSetScope()
    {
        $this->_model->setScope($this->_model->getAttribute());
        $this->assertEquals(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
            $this->_model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoConfigFixture current_store currency/options/base GBP
     */
    public function testAfterSave()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->load(1);
        $product->setOrigData();
        $product->setPrice(9.99);
        $product->setStoreId(0);

        $this->_model->setScope($this->_model->getAttribute());
        $this->_model->afterSave($product);

        $this->assertEquals(
            '9.99',
            $product->getResource()->getAttributeRawValue(
                $product->getId(),
                $this->_model->getAttribute()->getId(),
                Mage::app()->getStore()->getId()
            )
        );
    }
}
