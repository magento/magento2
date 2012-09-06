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
 * @package     performance_tests
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_InstallerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var Magento_Installer|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var
     */
    protected $_installerScript;

    protected function setUp()
    {
        $this->_shell = $this->getMock('Magento_Shell', array('execute'));
        $this->_installerScript = realpath(__DIR__ . '/_files/install_stub.php');
        $this->_object = $this->getMock(
            'Magento_Installer',
            array('_bootstrap', '_reindex', '_updateFilesystemPermissions'),
            array($this->_installerScript, $this->_shell)
        );
    }

    protected function tearDown()
    {
        unset($this->_shell);
        unset($this->_object);
    }

    /**
     * @param string $installerScriptPath
     * @dataProvider constructorExceptionDataProvider
     * @expectedException Magento_Exception
     */
    public function testConstructorException($installerScriptPath)
    {
        new Magento_Installer($installerScriptPath, $this->_shell);
    }

    public function constructorExceptionDataProvider()
    {
        return array(
            'non existing script' => array('non_existing_script'),
            'directory path' => array(__DIR__)
        );
    }

    public function testUninstall()
    {
        $this->_shell
            ->expects($this->once())
            ->method('execute')
            ->with('php -f %s -- --uninstall', array($this->_installerScript))
        ;
        $this->_object->uninstall();
    }

    public function testInstall()
    {
        $this->_shell
            ->expects($this->once())
            ->method('execute')
            ->with('php -f %s -- --option1 %s --option2 %s', array($this->_installerScript, 'value1', 'value 2'))
        ;
        $this->_object
            ->expects($this->once())
            ->method('_bootstrap')
        ;
        $this->_object
            ->expects($this->once())
            ->method('_reindex')
        ;
        $this->_object->install(array('option1' => 'value1', 'option2' => 'value 2'));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Fixture has been applied
     */
    public function testInstallFixtures()
    {
        $this->_object->install(array(), array(__DIR__ . '/_files/fixture.php'));
    }
}
