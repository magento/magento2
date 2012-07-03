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
 * @package     Mage_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_ImportExport_Model_Import_Entity_V2_Eav_Abstract
 */
class Mage_ImportExport_Model_Import_Entity_V2_Eav_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Model object which used for tests
     *
     * @var Mage_ImportExport_Model_Import_Entity_V2_Eav_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Create all necessary data for tests
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_Entity_V2_Eav_Abstract', array(),
            '', false);
    }

    /**
     * Unset created data during test
     */
    protected function tearDown()
    {
        unset($this->_model);
        parent::tearDown();
    }

    /**
     * Test for method getAttributeOptions()
     */
    public function testGetAttributeOptions()
    {
        $indexAttributeCode = 'gender';

        /** @var $attributeCollection Mage_Customer_Model_Resource_Attribute_Collection */
        $attributeCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection');
        $attributeCollection->addFieldToFilter(
            'attribute_code',
            array(
                'in' => array($indexAttributeCode, 'group_id')
            )
        );
        /** @var $attribute Mage_Customer_Model_Attribute */
        foreach ($attributeCollection as $attribute) {
            $index = ($attribute->getAttributeCode() == $indexAttributeCode) ? 'value' : 'label';
            $expectedOptions = array();
            foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                $expectedOptions[strtolower($option[$index])] = $option['value'];
            }

            $actualOptions = $this->_model->getAttributeOptions($attribute, array($indexAttributeCode));
            $this->assertSame($expectedOptions, $actualOptions);
        }
    }

    /**
     * Test for method _initWebsites()
     */
    public function testInitWebsitesWithoutBaseWebsite()
    {
        $method = new ReflectionMethod($this->_model, '_initWebsites');
        $method->setAccessible(true);
        $method->invoke($this->_model);
        $this->assertAttributeSame($this->_getWebsites(), '_websiteCodeToId', $this->_model);
    }

    /**
     * Test for method _initWebsites()
     */
    public function testInitWebsitesWithBaseWebsite()
    {
        $method = new ReflectionMethod($this->_model, '_initWebsites');
        $method->setAccessible(true);
        $method->invoke($this->_model, true);
        $this->assertAttributeSame($this->_getWebsites(true), '_websiteCodeToId', $this->_model);
    }

    /**
     * Get websites data for tests
     *
     * @param bool $withDefault
     * @return array
     */
    protected function _getWebsites($withDefault = false)
    {
        $websites = array();
        /** @var $website Mage_Core_Model_Website */
        foreach (Mage::app()->getWebsites($withDefault) as $website) {
            $websites[$website->getCode()] = $website->getId();
        }
        return $websites;
    }

    /**
     * Test for method _initStores()
     */
    public function testInitStoresWithoutBaseStore()
    {
        $method = new ReflectionMethod($this->_model, '_initStores');
        $method->setAccessible(true);
        $method->invoke($this->_model);
        $this->assertAttributeSame($this->_getStores(), '_storeCodeToId', $this->_model);
    }

    /**
     * Test for method _initStores()
     */
    public function testInitStoresWithBaseStore()
    {
        $method = new ReflectionMethod($this->_model, '_initStores');
        $method->setAccessible(true);
        $method->invoke($this->_model, true);
        $this->assertAttributeSame($this->_getStores(true), '_storeCodeToId', $this->_model);
    }

    /**
     * Get stores data for tests
     *
     * @param bool $withDefault
     * @return array
     */
    protected function _getStores($withDefault = false)
    {
        $stores = array();
        /** @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores($withDefault) as $store) {
            $stores[$store->getCode()] = $store->getId();
        }
        return $stores;
    }
}
