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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Magento_Test_Bootstrap.
 */
class Magento_Test_BootstrapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_magentoDir;
    protected static $_testsDir;
    protected static $_localXmlFile;
    protected static $_tmpDir;
    protected static $_globalEtcFiles;
    protected static $_moduleEtcFiles;

    /**
     * @var Magento_Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var Magento_Test_Db_DbAbstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_db;

    /**
     * @var Magento_Test_Bootstrap|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_bootstrap;

    /**
     * Calculate directories
     */
    public static function setUpBeforeClass()
    {
        self::$_magentoDir     = realpath(dirname(__FILE__) . '/../../../../../../../../..');
        self::$_testsDir       = realpath(dirname(__FILE__) . '/../../../../../..');
        self::$_localXmlFile   = realpath(dirname(__FILE__) . '/../../../../../../etc/local-mysql.xml.dist');
        self::$_globalEtcFiles = realpath(dirname(__FILE__) . '/../../../../../../../../../app/etc/*.xml');
        self::$_moduleEtcFiles = realpath(dirname(__FILE__) . '/../../../../../../../../../app/etc/modules/*.xml');
        self::$_tmpDir         = realpath(dirname(__FILE__) . '/../../../../../../tmp');
    }

    protected function setUp()
    {
        $this->_shell = $this->getMock('Magento_Shell', array('execute'));
        $this->_db = $this->getMock(
            'Magento_Test_Db_DbAbstract',
            array('cleanup'),
            array('host', 'user', 'password', 'schema', self::$_tmpDir, $this->_shell)
        );
        /* Suppress calling the constructor at this step */
        $this->_bootstrap = $this->getMock(
            'Magento_Test_Bootstrap',
            array(
                '_initialize',
                '_resetApp',
                '_verifyDirectories',
                '_instantiateDb',
                '_isInstalled',
                '_emulateEnvironment',
                '_ensureDirExists',
                '_install',
                '_cleanupFilesystem',
            ),
            array(),
            '',
            false
        );
        /* Setup expectations for methods that are being called within the constructor */
        $this->_bootstrap
            ->expects($this->any())
            ->method('_instantiateDb')
            ->will($this->returnValue($this->_db))
        ;
        /* Call constructor explicitly */
        $this->_callBootstrapConstructor();
    }

    protected function tearDown()
    {
        $this->_shell = null;
        $this->_db = null;
        $this->_bootstrap = null;
    }

    /**
     * Explicitly call the constructor method of the underlying bootstrap object
     *
     * @param string|null $localXmlFile
     * @param bool $isCleanupEnabled
     */
    protected function _callBootstrapConstructor($localXmlFile = null, $isCleanupEnabled = false)
    {
        $this->_bootstrap->__construct(
            self::$_magentoDir,
            self::$_testsDir,
            ($localXmlFile ? $localXmlFile : self::$_localXmlFile),
            self::$_globalEtcFiles,
            self::$_moduleEtcFiles,
            '',
            self::$_tmpDir,
            $this->_shell,
            $isCleanupEnabled
        );
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetInstance()
    {
        Magento_Test_Bootstrap::getInstance();
    }

    /**
     * @depends testGetInstance
     */
    public function testSetGetInstance()
    {
        Magento_Test_Bootstrap::setInstance($this->_bootstrap);
        $this->assertSame($this->_bootstrap, Magento_Test_Bootstrap::getInstance());
    }

    public function testCanTestHeaders()
    {
        if (!function_exists('xdebug_get_headers')) {
            $this->assertFalse(Magento_Test_Bootstrap::canTestHeaders(), 'Expected inability to test headers.');
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
        set_error_handler(function () use (&$expectedCanTest) {
            $expectedCanTest = false;
        });
        header($expectedHeader);
        setcookie('SomeCookie', 'cookie-value');
        restore_error_handler();

        $this->assertEquals($expectedCanTest, Magento_Test_Bootstrap::canTestHeaders());

        if ($expectedCanTest) {
            $actualHeaders = xdebug_get_headers();
            $this->assertContains($expectedHeader, $actualHeaders);
            $this->assertContains($expectedCookie, $actualHeaders);
        }
    }

    public function testConstructorInstallation()
    {
        $this->_bootstrap
            ->expects($this->atLeastOnce())
            ->method('_isInstalled')
            ->will($this->returnValue(false))
        ;
        $this->_bootstrap
            ->expects($this->once())
            ->method('_install')
        ;
        $this->_callBootstrapConstructor();
    }

    public function testConstructorInitialization()
    {
        $this->_bootstrap
            ->expects($this->atLeastOnce())
            ->method('_isInstalled')
            ->will($this->returnValue(true))
        ;
        $this->_bootstrap
            ->expects($this->once())
            ->method('_initialize')
        ;
        $this->_callBootstrapConstructor();
    }

    public function testConstructorCleanupDisabled()
    {
        $this->_db
            ->expects($this->never())
            ->method('cleanup')
        ;
        $this->_bootstrap
            ->expects($this->never())
            ->method('_cleanupFilesystem')
        ;
        $this->_callBootstrapConstructor(null, false);
    }

    public function testConstructorCleanupEnabled()
    {
        $this->_db
            ->expects($this->once())
            ->method('cleanup')
        ;
        $this->_bootstrap
            ->expects($this->once())
            ->method('_cleanupFilesystem')
        ;
        $this->_callBootstrapConstructor(null, true);
    }

    /**
     * @dataProvider constructorExceptionDataProvider
     * @expectedException Magento_Exception
     */
    public function testConstructorException($localXmlFile)
    {
        $this->_callBootstrapConstructor($localXmlFile);
    }

    public function constructorExceptionDataProvider()
    {
        return array(
            'non existing local.xml' => array('local-non-existing.xml'),
            'invalid local.xml'      => array(dirname(__FILE__) . '/Bootstrap/_files/local-invalid.xml'),
        );
    }

    /**
     * @dataProvider getDbVendorNameDataProvider
     */
    public function testGetDbVendorName($localXmlFile, $expectedDbVendorName)
    {
        $this->_callBootstrapConstructor($localXmlFile);
        $this->assertEquals($expectedDbVendorName, $this->_bootstrap->getDbVendorName());
    }

    public function getDbVendorNameDataProvider()
    {
        return array(
            'mysql'  => array(self::$_localXmlFile, 'mysql'),
            'custom' => array(realpath(__DIR__ . '/Bootstrap/_files/local-custom.xml'), 'mssql'),
        );
    }

    /**
     * @param $origParams
     * @param $customParams
     * @param $expectedResult
     * @dataProvider reinitializeDataProvider
     */
    public function testReinitialize($origParams, $customParams, $expectedResult)
    {

        $property = new ReflectionProperty(get_class($this->_bootstrap), '_initParams');
        $property->setAccessible(true);
        $property->setValue($this->_bootstrap, $origParams);

        $this->_bootstrap->expects($this->once())->method('_resetApp');
        $this->_bootstrap->expects($this->once())->method('_initialize')->with($expectedResult);

        $this->_bootstrap->reinitialize($customParams);
    }

    /**
     * @return array
     */
    public function reinitializeDataProvider()
    {
        $origParams = array('one' => array('two' => 'three'));
        return array(
            array(
                $origParams,
                array(),
                $origParams
            ),
            array(
                $origParams,
                array('one' => array('four' => 'five')),
                array('one' => array('two' => 'three', 'four' => 'five'))
            ),
            array(
                $origParams,
                array('one' => array('two' => 'five')),
                array('one' => array('two' => 'five'))
            ),
        );
    }

    public function testGetTestsDir()
    {
        $this->assertEquals(self::$_testsDir, $this->_bootstrap->getTestsDir());
    }

    public function testGetInitParams()
    {
        $initParams = $this->_bootstrap->getInitParams();
        $this->_bootstrap->expects($this->once())
            ->method('_initialize')
            ->with($initParams);
        $this->_bootstrap->expects($this->once())
            ->method('_isInstalled')
            ->will($this->returnValue(true));

        $this->_callBootstrapConstructor();
    }
}
