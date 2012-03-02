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
 * Test class for Mage_Catalog_Model_Product_Attribute_Api.
 *
 * @group module:Mage_Catalog
 */
class Mage_Catalog_Model_Product_Attribute_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Attribute_Api
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product_Attribute_Api;
    }

    public function testItems()
    {
        $items = $this->_model->items(4); /* default product attribute set after installation */
        $this->assertInternalType('array', $items);
        $element = current($items);
        $this->assertArrayHasKey('attribute_id', $element);
        $this->assertArrayHasKey('code', $element);
        $this->assertArrayHasKey('type', $element);
        $this->assertArrayHasKey('required', $element);
        $this->assertArrayHasKey('scope', $element);
        foreach ($items as $item) {
            if ($item['code'] == 'status') {
                return $item['attribute_id'];
            }
        }
        return false;
    }

    /**
     * @depends testItems
     */
    public function testOptions($attributeId)
    {
        if (!$attributeId) {
            $this->fail('Wromg attribute id');
        }
        $options = $this->_model->options($attributeId);
        $this->assertInternalType('array', $options);
        $element = current($options);
        $this->assertArrayHasKey('value', $element);
        $this->assertArrayHasKey('label', $element);
    }
}
