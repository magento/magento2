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
 * @package     Mage_Install
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_WizardControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @var string
     */
    protected static $_tmpDir;

    /**
     * @var array
     */
    protected static $_params = array();

    public static function setUpBeforeClass()
    {
        $tmpDir = Magento_Test_Helper_Bootstrap::getInstance()->getAppInstallDir() . DIRECTORY_SEPARATOR . __CLASS__;
        if (is_file($tmpDir)) {
            unlink($tmpDir);
        } elseif (is_dir($tmpDir)) {
            Varien_Io_File::rmdirRecursive($tmpDir);
        }
        // deliberately create a file instead of directory to emulate broken access to static directory
        touch($tmpDir);
        self::$_tmpDir = $tmpDir;

        // emulate invalid installation date, so that application will think it is not installed
        self::$_params = array(Mage::PARAM_CUSTOM_LOCAL_CONFIG
            => sprintf(Mage_Core_Model_Config_Primary::CONFIG_TEMPLATE_INSTALL_DATE, 'invalid')
        );
    }

    public function testPreDispatch()
    {
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(self::$_params);
        Mage::getObjectManager()->configure(array(
            'preferences' => array(
                'Mage_Core_Controller_Request_Http' => 'Magento_Test_Request',
                'Mage_Core_Controller_Response_Http' => 'Magento_Test_Response'
            )
        ));
        $this->dispatch('install/wizard');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * @param string $action
     * @dataProvider actionsDataProvider
     * @expectedException Magento_BootstrapException
     */
    public function testPreDispatchImpossibleToRenderPage($action)
    {
        $params = self::$_params;
        $params[Mage::PARAM_APP_DIRS][Mage_Core_Model_Dir::STATIC_VIEW] = self::$_tmpDir;
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize($params);
        Mage::getObjectManager()->configure(array(
            'preferences' => array(
                'Mage_Core_Controller_Request_Http' => 'Magento_Test_Request',
                'Mage_Core_Controller_Response_Http' => 'Magento_Test_Response'
            )
        ));
        $this->dispatch("install/wizard/{$action}");
    }

    /**
     * @return array
     */
    public function actionsDataProvider()
    {
        return array(
            array('index'),
            array('begin'),
            array('beginPost'),
            array('locale'),
            array('localeChange'),
            array('localePost'),
            array('download'),
            array('downloadPost'),
            array('downloadAuto'),
            array('install'),
            array('downloadManual'),
            array('config'),
            array('configPost'),
            array('installDb'),
            array('administrator'),
            array('administratorPost'),
            array('end'),
        );
    }
}
