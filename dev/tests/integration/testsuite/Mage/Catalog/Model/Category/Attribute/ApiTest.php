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
 * Test class for Mage_Catalog_Model_Category_Attribute_Api.
 */
class Mage_Catalog_Model_Category_Attribute_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Category_Attribute_Api
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Category_Attribute_Api;
    }

    public function testItems()
    {
        $attributes = $this->_model->items();
        $this->assertNotEmpty($attributes);
        $attribute = array_shift($attributes);
        $this->assertContains('attribute_id', array_keys($attribute));
        $this->assertContains('code', array_keys($attribute));
    }

    /**
     * Internal assert that validate options structure
     *
     * @param array $options
     */
    protected function _assertOptionsStructure(array $options)
    {
        $first = current($options);
        $this->assertArrayHasKey('value', $first);
        $this->assertArrayHasKey('label', $first);
    }

    public function testLayoutOptions()
    {
        $options = $this->_model->options('page_layout');
        $this->assertNotEmpty($options);
        $this->_assertOptionsStructure($options);
    }

    public function testModeOptions()
    {
        $options = $this->_model->options('display_mode');
        $this->assertNotEmpty($options);
        $this->_assertOptionsStructure($options);
    }

    public function testPageOptions()
    {
        $options = $this->_model->options('landing_page');
        $this->assertNotEmpty($options);
        $this->_assertOptionsStructure($options);
    }

    public function testSortByOptions()
    {
        $options = $this->_model->options('available_sort_by');
        $this->assertNotEmpty($options);
        $this->_assertOptionsStructure($options);
    }

    /**
     * @expectedException Mage_Api_Exception
     */
    public function testFault()
    {
        $this->_model->options('not_exists');
    }
}
