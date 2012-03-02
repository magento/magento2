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
 * @group module:Mage_Catalog
 */
class Mage_Catalog_Model_Product_Type_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $class
     * @dataProvider itemsDataProvider
     */
    public function testItems($class)
    {
        $model = new $class;
        $result = $model->items();
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        foreach ($result as $item) {
            $this->assertArrayHasKey('type', $item);
            $this->assertArrayHasKey('label', $item);
        }
    }

    public function itemsDataProvider()
    {
        return array(
            array('Mage_Catalog_Model_Product_Type_Api'),
            array('Mage_Catalog_Model_Product_Type_Api_V2'), // a dummy class, doesn't require separate test suite
        );
    }
}
