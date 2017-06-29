<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

        $expectedResult = ['HTTP_HOST' => 'localhost', 'SCRIPT_FILENAME' => 'index.php'];
        $actualResult = ['HTTP_HOST' => '127.0.0.1'];
        $this->_object->emulateHttpRequest($actualResult);
        $this->assertEquals($expectedResult, $actualResult);

        $this->assertSame($serverVars, $_SERVER, 'Super-global $_SERVER must not be affected.');
    }

    public function testEmulateSession()
    {
        $sessionVars = $this->_getSessionVars();
        $this->assertEmpty(session_id());

        $actualResult = ['session_data_to_be_erased' => 'some_value'];
        $this->_object->emulateSession($actualResult);
        $this->assertEquals([], $actualResult);

        $this->assertSame($sessionVars, $this->_getSessionVars(), 'Super-global $_SESSION must not be affected.');
        $this->assertNotEmpty(session_id(), 'Global session identified has to be emulated.');
    }
}
