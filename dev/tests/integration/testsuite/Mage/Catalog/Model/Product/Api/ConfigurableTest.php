<?php
/**
 * Test configurable product API
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
 * @method Mage_Catalog_Model_Product_Api_Helper_Configurable _getHelper()
 * @magentoDbIsolation enabled
 */
class Mage_Catalog_Model_Product_Api_ConfigurableTest extends Mage_Catalog_Model_Product_Api_TestCaseAbstract
{
    /**
     * Default helper for current test suite
     *
     * @var string
     */
    protected $_defaultHelper = 'Mage_Catalog_Model_Product_Api_Helper_Configurable';

    /**
     * Test successful configurable product create.
     * Scenario:
     * 1. Create EAV attributes and attribute set usable for configurable.
     * 2. Send request to create product with type 'configurable' and all valid attributes data.
     * Expected result:
     * Load product and assert it was created correctly.
     */
    public function testCreate()
    {
        $productData = $this->_getHelper()->getValidCreateData();
        $productId = $this->_createProductWithApi($productData);
        // Validate outcome
        /** @var $actual Mage_Catalog_Model_Product */
        $actual = Mage::getModel('Mage_Catalog_Model_Product')->load($productId);
        $this->_getHelper()->checkConfigurableAttributesData(
            $actual,
            $productData['configurable_attributes'],
            false
        );
        unset($productData['configurable_attributes']);
        $expected = Mage::getModel('Mage_Catalog_Model_Product');
        $expected->setData($productData);
        $this->assertProductEquals($expected, $actual);
    }
}
