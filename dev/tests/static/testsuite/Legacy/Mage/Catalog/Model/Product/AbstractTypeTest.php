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
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for obsolete methods in Product Type instances
 *
 * Abstract class is needed because it is not possible to run both tests of inherited class and its inheritors
 * @see https://github.com/sebastianbergmann/phpunit/issues/385
 */
abstract class Legacy_Mage_Catalog_Model_Product_AbstractTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $_productTypeFiles = array();

    /**
     * @dataProvider obsoleteMethodsDataProvider
     *
     * @param string $method
     */
    public function testProductTypeModelsForObsoleteMethods($method)
    {
        $root = Utility_Files::init()->getPathToSource();
        foreach ($this->_productTypeFiles as $file) {
            $this->assertNotContains(
                '$this->' . $method . '(',
                file_get_contents($root . $file),
                "Method 'Mage_Catalog_Model_Product_Type_Abstract::$method' is obsolete."
            );
        }
    }

    /**
     * @return array
     */
    public static function obsoleteMethodsDataProvider()
    {
        return array(
            array('getProduct'),
            array('setProduct'),
        );
    }
}
