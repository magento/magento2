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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_Model_EntryPoint_ConsoleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Install_Model_EntryPoint_Console
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Magento_ObjectManager
     */
    protected $_objectManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Config_Primary
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Install_Model_Installer_Console
     */
    protected $_installerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Dir_Verification
     */
    protected $_dirVerifierMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Install_Model_EntryPoint_Output
     */
    protected $_outputMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager');
        $this->_configMock = $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false);
        $this->_installerMock = $this->getMock('Mage_Install_Model_Installer_Console', array(), array(), '', false);
        $this->_dirVerifierMock = $this->getMock('Mage_Core_Model_Dir_Verification', array(), array(), '', false);
        $this->_outputMock = $this->getMock('Mage_Install_Model_EntryPoint_Output', array(), array(), '', false);

        $this->_objectManagerMock->expects($this->once())->method('create')
            ->with('Mage_Install_Model_Installer_Console')
            ->will($this->returnValue($this->_installerMock));

        $this->_objectManagerMock->expects($this->once())->method('get')
            ->with('Mage_Core_Model_Dir_Verification')
            ->will($this->returnValue($this->_dirVerifierMock));
    }

    protected function _createModel($params = array())
    {
        return $this->getMock(
            'Mage_Install_Model_EntryPoint_Console',
            array('_setGlobalObjectManager'),
            array('', $params, $this->_configMock, $this->_objectManagerMock, $this->_outputMock)
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_configMock);
        unset($this->_objectManagerMock);
        unset($this->_installerMock);
        unset($this->_dirVerifierMock);
    }

    /**
     * @param string $param
     * @param string $method
     * @param string $testValue
     * @dataProvider processRequestShowsRequestedDataProvider
     */
    public function testProcessRequestShowsRequestedData($param, $method, $testValue)
    {
        $model = $this->_createModel(array($param => true));
        $this->_installerMock
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($testValue));
        $this->_outputMock->expects($this->once())->method('export')->with($testValue);
        $model->processRequest();
    }

    public function processRequestShowsRequestedDataProvider()
    {
        return array(
            array('show_locales', 'getAvailableLocales', 'locales'),
            array('show_currencies', 'getAvailableCurrencies', 'currencies'),
            array('show_timezones', 'getAvailableTimezones', 'timezones'),
            array('show_install_options', 'getAvailableInstallOptions', 'install_options'),
        );
    }

    public function testInstallReportsSuccessMessage()
    {
        $model = $this->_createModel(array());
        $this->_outputMock->expects($this->once())->method('success')->with($this->stringContains('successfully'));
        $model->processRequest();
    }

    public function testInstallReportsEncryptionKey()
    {
        $model = $this->_createModel(array());
        $this->_installerMock->expects($this->once())->method('install')->will($this->returnValue('enc_key'));
        $this->_outputMock->expects($this->once())->method('success')->with($this->stringContains('enc_key'));
        $model->processRequest();
    }

    public function testUninstallReportsSuccess()
    {
        $model = $this->_createModel(array('uninstall' => true));
        $this->_installerMock->expects($this->once())->method('uninstall')->will($this->returnValue(true));
        $this->_outputMock->expects($this->once())->method('success')->with($this->stringContains('Uninstalled'));
        $model->processRequest();
    }

    public function testUninstallReportsIgnoreIfApplicationIsNotInstalled()
    {
        $model = $this->_createModel(array('uninstall' => true));
        $this->_installerMock->expects($this->once())->method('uninstall')->will($this->returnValue(false));
        $this->_outputMock->expects($this->once())->method('success')->with($this->stringContains('non-installed'));
        $model->processRequest();
    }

    public function testProcessRequestReportsErrors()
    {
        $model = $this->_createModel(array('uninstall' => true));
        $this->_installerMock->expects($this->once())->method('hasErrors')->will($this->returnValue(true));
        $this->_installerMock->expects($this->once())->method('getErrors')->will($this->returnValue(array('error1')));
        $this->_outputMock->expects($this->once())->method('error')->with($this->stringContains('error1'));
        $model->processRequest();
    }

    public function testProcessRequestLoadsExtraConfig()
    {
        $model = $this->_createModel(array('config' => realpath(__DIR__ . '/_files/config.php')));
        $this->_installerMock->expects($this->once())->method('uninstall')->will($this->returnValue(true));
        $model->processRequest();
    }
}
