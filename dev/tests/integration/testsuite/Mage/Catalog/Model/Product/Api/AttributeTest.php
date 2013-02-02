<?php
/**
 * Test API getting orders list method
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
class Mage_Catalog_Model_Product_Api_AttributeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests attribute creation with invalid characters in attribute code (possible SQL injection)
     */
    public function testCreateWithInvalidCode()
    {
        $attributeData = array(
            'attribute_code' => 'mytest1.entity_id = e.entity_id); DROP TABLE aaa_test;',
            'scope' => 'global',
            'frontend_input' => 'select',
            'frontend_label' => array(
                array('store_id' => 0, 'label' => 'My Attribute With SQL Injection')
            )
        );

        try {
            Magento_Test_Helper_Api::call($this, 'catalogProductAttributeCreate', array('data' => $attributeData));

            $this->fail('Exception with message like "invalid attribute code" expected but not thrown');
        } catch (Exception $e) {
            $this->assertEquals(103, $e->faultcode, 'Unexpected fault code');
            $this->assertEquals(
                'Attribute code is invalid. Please use only letters (a-z), numbers (0-9), '
                    . 'or underscore(_) in this field. First character should be a letter.',
                $e->getMessage(),
                'Unexpected exception messsage'
            );
        }
    }
}
