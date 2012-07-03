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
 * Tests for block Mage_ImportExport_Block_Adminhtml_Import_BeforeTest
 */
class Mage_ImportExport_Block_Adminhtml_Import_BeforeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested source model
     *
     * @var Mage_ImportExport_Model_Source_Format_Version
     */
    public static $sourceModel;

    /**
     * Helper registry key
     *
     * @var string
     */
    protected static $_helperKey = '_helper/Mage_ImportExport_Helper_Data';

    /**
     * Mock helper
     *
     * @static
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Mage::unregister(self::$_helperKey);
        Mage::register(self::$_helperKey, new Mage_ImportExport_Helper_Data());
    }

    /**
     * Unregister helper
     *
     * @static
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        Mage::unregister(self::$_helperKey);
    }

    /**
     * Test getter for JS array behaviour string
     */
    public function testGetJsAllowedCustomerBehaviours()
    {
        /** @var $helper Mage_ImportExport_Helper_Data */
        $helper = Mage::helper('Mage_ImportExport_Helper_Data');
        $existingVersion = 'existing_version';
        $customerBehaviours = array('behaviour_1', 'behaviour_2');
        $notExistingVersion = 'not_existing_version';

        $reflectionBehaviours = new ReflectionProperty('Mage_ImportExport_Helper_Data', '_allowedCustomerBehaviours');
        $reflectionBehaviours->setAccessible(true);
        $reflectionBehaviours->setValue($helper, array($existingVersion => $customerBehaviours));

        $block = new Mage_ImportExport_Block_Adminhtml_Import_Before();

        $testJsBehaviours = $block->getJsAllowedCustomerBehaviours($existingVersion);
        $correctJsBehaviours = Zend_Json::encode($customerBehaviours);
        $this->assertEquals($correctJsBehaviours, $testJsBehaviours, 'Incorrect JS array string.');

        $testJsBehaviours = $block->getJsAllowedCustomerBehaviours($notExistingVersion);
        $correctJsBehaviours = Zend_Json::encode(array());
        $this->assertEquals($correctJsBehaviours, $testJsBehaviours, 'Incorrect JS array string.');
    }
}
