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
 * Test class for \Magento\TestFramework\Bootstrap\Environment.
 */
namespace Magento\Test\Bootstrap;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_sessionId = '';

    /**
     * @var \Magento\TestFramework\Bootstrap\Environment
     */
    protected $_object;

    public static function setUpBeforeClass()
    {
        self::$_sessionId = session_id();
    }

    public static function tearDownAfterClass()
    {
        session_id(self::$_sessionId);
    }

    protected function setUp()
    {
        $this->_object = new \Magento\TestFramework\Bootstrap\Environment();
    }

    protected function tearDown()
    {
        $this->_object = null;
    }

    /**
     * Retrieve the current session's variables
     *
     * @return array|null
     */
    protected function _getSessionVars()
    {
        return isset($_SESSION) ? $_SESSION : null;
    }

    public function testEmulateHttpRequest()
    {
        $serverVars = $_SERVER;
        $this->assertNotEmpty($serverVars);

        $expectedResult = array('HTTP_HOST' => 'localhost', 'SCRIPT_FILENAME' => 'index.php');
        $actualResult = array('HTTP_HOST' => '127.0.0.1');
        $this->_object->emulateHttpRequest($actualResult);
        $this->assertEquals($expectedResult, $actualResult);

        $this->assertSame($serverVars, $_SERVER, 'Super-global $_SERVER must not be affected.');
    }

    public function testEmulateSession()
    {
        $sessionVars = $this->_getSessionVars();
        $this->assertEmpty(session_id());

        $actualResult = array('session_data_to_be_erased' => 'some_value');
        $this->_object->emulateSession($actualResult);
        $this->assertEquals(array(), $actualResult);

        $this->assertSame($sessionVars, $this->_getSessionVars(), 'Super-global $_SESSION must not be affected.');
        $this->assertNotEmpty(session_id(), 'Global session identified has to be emulated.');
    }
}
