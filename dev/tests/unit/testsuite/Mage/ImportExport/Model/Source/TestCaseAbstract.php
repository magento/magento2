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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract class for import/export source models
 */
abstract class Mage_ImportExport_Model_Source_TestCaseAbstract extends PHPUnit_Framework_TestCase
{
    /**
     * Tested source model
     */
    public static $sourceModel;

    /**
     * Helper registry key
     *
     * @var string
     */
    protected static $_helperKey = '_helper/Mage_ImportExport_Helper_Data';

    /**
     * Helper property
     *
     * @var Mage_ImportExport_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected static $_helper;

    /**
     * Unregister source model and helper
     *
     * @static
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        Mage::unregister(self::$_helperKey);
        self::$_helper = null;
        self::$sourceModel = null;
    }

    /**
     * Helper initialization
     *
     * @return Mage_ImportExport_Helper_Data
     */
    protected function _initHelper()
    {
        if (!self::$_helper) {
            self::$_helper = $this->getMock(
                'Mage_ImportExport_Helper_Data',
                array('__')
            );
            self::$_helper->expects($this->any())
                ->method('__')
                ->will($this->returnArgument(0));

            Mage::unregister(self::$_helperKey);
            Mage::register(self::$_helperKey, self::$_helper);
        }
        return self::$_helper;
    }
}
