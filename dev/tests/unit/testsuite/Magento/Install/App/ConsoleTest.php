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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Install\App;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Install\App\Console
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Install\Model\Installer\Console
     */
    protected $_installerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\App\Filesystem\DirectoryList\Verification
     */
    protected $_dirVerifierMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Install\App\Output
     */
    protected $_outputMock;

    /** \PHPUnit_Framework_MockObject_MockObject|\Magento\Install\Model\Installer\ConsoleFactory */
    protected $_instFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    protected function setUp()
    {
        $this->_instFactoryMock = $this->getMock('\Magento\Install\Model\Installer\ConsoleFactory',
            array('create'), array(), '', false);
        $this->_installerMock = $this->getMock('Magento\Install\Model\Installer\Console', array(), array(), '', false);
        $this->_dirVerifierMock = $this->getMock(
            'Magento\App\Filesystem\DirectoryList\Verification',
            array(),
            array(),
            '',
            false
        );
        $this->_outputMock = $this->getMock('Magento\Install\App\Output', array(), array(), '', false);
        $this->_appStateMock = $this->getMock('Magento\App\State', array(), array(), '', false);
        $this->_configLoaderMock = $this->getMockBuilder('Magento\App\ObjectManager\ConfigLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_instFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($this->_installerMock));

        $this->_configLoaderMock->expects($this->once())->method('load')
            ->with('install')->will($this->returnValue(array('di' => 'config')));

        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_objectManagerMock->expects($this->once())->method('configure')->with(array('di' => 'config'));
    }

    protected function _createModel($params = array())
    {
        $directory = $this->getMock(
            'Magento\Filesystem\Directory\Read',
            array('isExist','getRelativePath'),
            array(),
            '',
            false
        );
        $filesystem = $this->getMock(
            'Magento\App\Filesystem',
            array('getDirectoryRead', '__wakeup'),
            array(),
            '',
            false
        );
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(\Magento\App\Filesystem::ROOT_DIR)
            ->will($this->returnValue($directory));
        if (isset($params['config'])) {
            $directory->expects($this->once())
                ->method('getRelativePath')
                ->with($params['config'])
                ->will($this->returnValue($params['config']));
            $directory->expects($this->once())
                ->method('isExist')
                ->with($params['config'])
                ->will($this->returnValue(true));
        }
        $this->_responseMock = $this->getMock('Magento\App\Console\Response', array(), array(), '', false);
        return new \Magento\Install\App\Console(
            $this->_instFactoryMock,
            $this->_outputMock,
            $this->_appStateMock,
            $this->_configLoaderMock,
            $this->_objectManagerMock,
            $filesystem,
            $this->_responseMock,
            $params
        );
    }

    /**
     * @param string $param
     * @param string $method
     * @param string $testValue
     * @dataProvider executeShowsRequestedDataProvider
     */
    public function testLaunchShowsRequestedData($param, $method, $testValue)
    {
        $model = $this->_createModel(array($param => true));
        $this->_installerMock
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($testValue));
        $this->_outputMock->expects($this->once())->method('export')->with($testValue);
        $this->assertEquals($this->_responseMock, $model->launch());
    }

    public function executeShowsRequestedDataProvider()
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
        $this->assertEquals($this->_responseMock, $model->launch());
    }

    public function testInstallReportsEncryptionKey()
    {
        $model = $this->_createModel(array());
        $this->_installerMock->expects($this->once())->method('install')->will($this->returnValue('enc_key'));
        $this->_outputMock->expects($this->once())->method('success')->with($this->stringContains('enc_key'));
        $this->assertEquals($this->_responseMock, $model->launch());
    }

    public function testUninstallReportsSuccess()
    {
        $model = $this->_createModel(array('uninstall' => true));
        $this->_installerMock->expects($this->once())->method('uninstall')->will($this->returnValue(true));
        $this->_outputMock->expects($this->once())->method('success')->with($this->stringContains('Uninstalled'));
        $this->assertEquals($this->_responseMock, $model->launch());
    }

    public function testUninstallReportsIgnoreIfApplicationIsNotInstalled()
    {
        $model = $this->_createModel(array('uninstall' => true));
        $this->_installerMock->expects($this->once())->method('uninstall')->will($this->returnValue(false));
        $this->_outputMock->expects($this->once())->method('success')->with($this->stringContains('non-installed'));
        $this->assertEquals($this->_responseMock, $model->launch());
    }

    public function testExecuteReportsErrors()
    {
        $model = $this->_createModel(array('uninstall' => true));
        $this->_installerMock->expects($this->once())->method('hasErrors')->will($this->returnValue(true));
        $this->_installerMock->expects($this->once())->method('getErrors')->will($this->returnValue(array('error1')));
        $this->_outputMock->expects($this->once())->method('error')->with($this->stringContains('error1'));
        $this->assertEquals($this->_responseMock, $model->launch());
    }

    public function testExecuteLoadsExtraConfig()
    {
        $model = $this->_createModel(array('config' => realpath(__DIR__ . '/_files/config.php')));
        $this->_installerMock->expects($this->once())->method('uninstall')->will($this->returnValue(true));
        $this->assertEquals($this->_responseMock, $model->launch());
    }
}
