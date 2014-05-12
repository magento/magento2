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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Model\Installer;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_tmpConfigFile = 'local.xml';

    /**
     * @var \Magento\Install\Model\Installer\Config
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directoryMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    protected function setUp()
    {
        $this->_directoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array(),
            array(),
            '',
            false
        );

        $this->_filesystemMock = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $this->_filesystemMock->expects(
            $this->any()
        )->method(
            'getPath'
        )->with(
            \Magento\Framework\App\Filesystem::CONFIG_DIR
        )->will(
            $this->returnValue(TESTS_TEMP_DIR)
        );
        $this->_filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($this->_directoryMock)
        );

        $this->_messageManager = $this->getMock(
            '\Magento\Framework\Message\ManagerInterface',
            array(),
            array(),
            '',
            false
        );
        $this->_model = new \Magento\Install\Model\Installer\Config(
            $this->getMock('Magento\Install\Model\Installer', array(), array(), '', false),
            $this->getMock('Magento\Framework\App\RequestInterface', array(), array(), '', false),
            $this->_filesystemMock,
            $this->getMock('Magento\Store\Model\StoreManagerInterface', array(), array(), '', false),
            $this->_messageManager
        );
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testReplaceTmpInstallDate()
    {
        $datePlaceholder = \Magento\Install\Model\Installer\Config::TMP_INSTALL_DATE_VALUE;
        $fixtureConfigData = "<date>{$datePlaceholder}</date>";
        $expectedConfigData = '<date>Sat, 19 Jan 2013 18:50:39 -0800</date>';

        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $this->equalTo($this->_tmpConfigFile)
        )->will(
            $this->returnValue($fixtureConfigData)
        );
        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'writeFile'
        )->with(
            $this->equalTo($this->_tmpConfigFile),
            $this->equalTo($expectedConfigData)
        )->will(
            $this->returnValue($fixtureConfigData)
        );

        $this->_model->replaceTmpInstallDate('Sat, 19 Jan 2013 18:50:39 -0800');
    }

    public function testReplaceTmpEncryptKey()
    {
        $keyPlaceholder = \Magento\Install\Model\Installer\Config::TMP_ENCRYPT_KEY_VALUE;
        $fixtureConfigData = "<key>{$keyPlaceholder}</key>";
        $expectedConfigData = '<key>3c7cf2e909fd5e2268a6e1539ae3c835</key>';

        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $this->equalTo($this->_tmpConfigFile)
        )->will(
            $this->returnValue($fixtureConfigData)
        );
        $this->_directoryMock->expects(
            $this->once()
        )->method(
            'writeFile'
        )->with(
            $this->equalTo($this->_tmpConfigFile),
            $this->equalTo($expectedConfigData)
        )->will(
            $this->returnValue($fixtureConfigData)
        );

        $this->_model->replaceTmpEncryptKey('3c7cf2e909fd5e2268a6e1539ae3c835');
    }
}
