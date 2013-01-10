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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_Source_Urlrewrite_OptionsTest.
 */
class Mage_Core_Model_Source_Urlrewrite_OptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Initialize helper
     */
    protected function setUp()
    {
        $helper = $this->getMockBuilder('Mage_Adminhtml_Helper_Data')
            ->setMethods(array('__'))
            ->disableOriginalConstructor()
            ->getMock();
        $helper->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));
        Mage::register('_helper/Mage_Adminhtml_Helper_Data', $helper);
    }

    /**
     * Clear helper
     */
    protected function tearDown()
    {
        Mage::unregister('_helper/Mage_Adminhtml_Helper_Data');
    }

    /**
     * @covers Mage_Core_Model_Source_Urlrewrite_OptionsTest::getAllOptions
     */
    public function testGetAllOptions()
    {
        $model = new Mage_Core_Model_Source_Urlrewrite_Options();
        $options = $model->getAllOptions();
        $this->assertInternalType('array', $options);
        $expectedOptions = array(
            '' => 'No',
            'R' => 'Temporary (302)',
            'RP' => 'Permanent (301)'
        );
        $this->assertEquals($expectedOptions, $options);
    }
}
