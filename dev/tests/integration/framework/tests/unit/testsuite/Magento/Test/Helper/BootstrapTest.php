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

/**
 * Test class for \Magento\TestFramework\Helper\Bootstrap.
 */
namespace Magento\Test\Helper;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\Bootstrap
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\Bootstrap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_bootstrap;

    /**
     * @var \Magento\TestFramework\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_application;

    /**
     * Predefined application initialization parameters
     *
     * @var array
     */
    protected $_fixtureInitParams = array(
        \Magento\Framework\App\Filesystem::PARAM_APP_DIRS => array(
            \Magento\Framework\App\Filesystem::CONFIG_DIR => array('path' => __DIR__),
            \Magento\Framework\App\Filesystem::VAR_DIR => array('path' => __DIR__)
        )
    );

    protected function setUp()
    {
        $this->_application = $this->getMock(
            'Magento\TestFramework\Application',
            array('getInstallDir', 'getInitParams', 'reinitialize', 'run'),
            array(),
            '',
            false
        );
        $this->_bootstrap = $this->getMock(
            'Magento\TestFramework\Bootstrap',
            array('getApplication', 'getDbVendorName'),
            array(),
            '',
            false
        );
        $this->_bootstrap->expects(
            $this->any()
        )->method(
            'getApplication'
        )->will(
            $this->returnValue($this->_application)
        );
        $this->_object = new \Magento\TestFramework\Helper\Bootstrap($this->_bootstrap);
    }

    protected function tearDown()
    {
        $this->_application = null;
        $this->_bootstrap = null;
        $this->_object = null;
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Helper instance is not defined yet.
     */
    public function testGetInstanceEmptyProhibited()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance();
    }

    public function testSetInstanceFirstAllowed()
    {
        \Magento\TestFramework\Helper\Bootstrap::setInstance($this->_object);
        return $this->_object;
    }

    /**
     * @depends testSetInstanceFirstAllowed
     */
    public function testGetInstanceAllowed(\Magento\TestFramework\Helper\Bootstrap $expectedInstance)
    {
        $this->assertSame($expectedInstance, \Magento\TestFramework\Helper\Bootstrap::getInstance());
    }

    /**
     * @depends testSetInstanceFirstAllowed
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Helper instance cannot be redefined.
     */
    public function testSetInstanceChangeProhibited()
    {
        \Magento\TestFramework\Helper\Bootstrap::setInstance($this->_object);
    }

    public function testCanTestHeaders()
    {
        if (!function_exists('xdebug_get_headers')) {
            $this->assertFalse(
                \Magento\TestFramework\Helper\Bootstrap::canTestHeaders(),
                'Expected inability to test headers.'
            );
            return;
        }
        $expectedHeader = 'SomeHeader: header-value';
        $expectedCookie = 'Set-Cookie: SomeCookie=cookie-value';

        /* Make sure that chosen reference samples are unique enough to rely on them */
        $actualHeaders = xdebug_get_headers();
        $this->assertNotContains($expectedHeader, $actualHeaders);
        $this->assertNotContains($expectedCookie, $actualHeaders);

        /* Determine whether header-related functions can be in fact called with no error */
        $expectedCanTest = true;
        set_error_handler(
            function () use (&$expectedCanTest) {
                $expectedCanTest = false;
            }
        );
        header($expectedHeader);
        setcookie('SomeCookie', 'cookie-value');
        restore_error_handler();

        $this->assertEquals($expectedCanTest, \Magento\TestFramework\Helper\Bootstrap::canTestHeaders());

        if ($expectedCanTest) {
            $actualHeaders = xdebug_get_headers();
            $this->assertContains($expectedHeader, $actualHeaders);
            $this->assertContains($expectedCookie, $actualHeaders);
        }
    }

    public function testGetAppInstallDir()
    {
        $this->_application->expects($this->once())->method('getInstallDir')->will($this->returnValue(__DIR__));
        $this->assertEquals(__DIR__, $this->_object->getAppInstallDir());
    }

    public function testGetAppInitParams()
    {
        $this->_application->expects(
            $this->once()
        )->method(
            'getInitParams'
        )->will(
            $this->returnValue($this->_fixtureInitParams)
        );
        $this->assertEquals($this->_fixtureInitParams, $this->_object->getAppInitParams());
    }

    public function testGetDbVendorName()
    {
        $this->_bootstrap->expects($this->once())->method('getDbVendorName')->will($this->returnValue('mysql'));
        $this->assertEquals('mysql', $this->_object->getDbVendorName());
    }

    public function testReinitialize()
    {
        $this->_application->expects($this->once())->method('reinitialize')->with($this->_fixtureInitParams);
        $this->_object->reinitialize($this->_fixtureInitParams);
    }

    public function testRunApp()
    {
        $this->_application->expects($this->once())->method('run');
        $this->_object->runApp();
    }
}
