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

class Mage_Core_Model_App_OptionsTest extends PHPUnit_Framework_TestCase
{
    public function testGetRunCode()
    {
        $model = new Mage_Core_Model_App_Options(array());
        $this->assertEmpty($model->getRunCode());

        $model = new Mage_Core_Model_App_Options(array(Mage_Core_Model_App_Options::OPTION_APP_RUN_CODE => 'test'));
        $this->assertEquals('test', $model->getRunCode());
    }

    public function testGetRunType()
    {
        $model = new Mage_Core_Model_App_Options(array());
        $this->assertEquals(Mage_Core_Model_App_Options::APP_RUN_TYPE_STORE, $model->getRunType());

        $runType = Mage_Core_Model_App_Options::APP_RUN_TYPE_WEBSITE;
        $model = new Mage_Core_Model_App_Options(array(Mage_Core_Model_App_Options::OPTION_APP_RUN_TYPE => $runType));
        $this->assertEquals($runType, $model->getRunType());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage run type "invalid" is not recognized, supported values: "store", "group", "website"
     */
    public function testGetRunTypeException()
    {
        new Mage_Core_Model_App_Options(array(Mage_Core_Model_App_Options::OPTION_APP_RUN_TYPE => 'invalid'));
    }

    public function testGetRunOptions()
    {
        $model = new Mage_Core_Model_App_Options(array('ignored_option' => 'ignored value'));
        $this->assertEmpty($model->getRunOptions());

        $extraLocalConfigFile = 'test/local.xml';
        $inputOptions = array(Mage_Core_Model_App_Options::OPTION_LOCAL_CONFIG_EXTRA_FILE => $extraLocalConfigFile);
        $expectedRunOptions = array(Mage_Core_Model_Config::OPTION_LOCAL_CONFIG_EXTRA_FILE => $extraLocalConfigFile);
        $model = new Mage_Core_Model_App_Options($inputOptions);
        $this->assertEquals($expectedRunOptions, $model->getRunOptions());
    }
}
