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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Model_Session_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Session_Abstract
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = $this->getMockForAbstractClass('Mage_Core_Model_Session_Abstract');
    }

    public function testGetCookie()
    {
        $cookie = $this->_model->getCookie();
        $this->assertInstanceOf('Mage_Core_Model_Cookie', $cookie);
        $this->assertSame($cookie, $this->_model->getCookie());
    }

    public function testInit()
    {
        $this->_model->init('test');
        $this->_model->setTestData('test');
        $data = $this->_model->getData();
        $this->assertArrayHasKey('test_data', $data);
        $this->assertSame($_SESSION['test'], $data);
    }

    public function testGetData()
    {
        $this->_model->setData(array('test_key' => 'test_value'));
        $this->assertEquals('test_value', $this->_model->getData('test_key', true));
        $this->assertNull($this->_model->getData('test_key'));
    }

    public function testGetSessionId()
    {
        $this->assertEquals(session_id(), $this->_model->getSessionId());
    }

    public function testGetSessionName()
    {
        $this->assertEquals(session_name(), $this->_model->getSessionName());
    }

    public function testSetSessionName()
    {
        $this->_model->setSessionName('test');
        $this->assertEquals('test', $this->_model->getSessionName());
    }

    public function testUnsetAll()
    {
        $data = array('key' => 'value');
        $this->_model->setData($data);

        $this->assertEquals($data, $this->_model->getData());
        $this->_model->unsetAll();

        $this->assertEquals(array(), $this->_model->getData());
    }

    public function testValidate()
    {
        $this->assertInstanceOf('Mage_Core_Model_Session_Abstract', $this->_model->validate());
    }

    public function testGetValidateHttpUserAgentSkip()
    {
        $agents = $this->_model->getValidateHttpUserAgentSkip();
        $this->assertContains('Shockwave Flash', $agents);
        $this->assertContains('Adobe Flash Player\s{1,}\w{1,10}', $agents);
    }

    public function testSetSessionId()
    {
        $sessionId = $this->_model->getSessionId();
        $this->_model->setSessionId();
        $this->assertEquals($sessionId, $this->_model->getSessionId());

        $this->_model->setSessionId('test');
        $this->assertEquals('test', $this->_model->getSessionId());
    }

    /**
     * @magentoConfigFixture current_store web/session/use_frontend_sid 1
     */
    public function testSetSessionIdFromParam()
    {
        $this->assertNotEquals('test_id', $this->_model->getSessionId());
        $_GET[$this->_model->getSessionIdQueryParam()] = 'test-id';
        $this->_model->setSessionId();

        $this->assertEquals('test-id', $this->_model->getSessionId());

        /* Use not valid identifier */
        $_GET[$this->_model->getSessionIdQueryParam()] = 'test_id';
        $this->_model->setSessionId();
        $this->assertEquals('test-id', $this->_model->getSessionId());
    }

    public function testGetEncryptedSessionId()
    {
        $sessionId = $this->_model->getEncryptedSessionId();
        $this->_model->setSessionId('new-id');
        $this->assertEquals($sessionId, $this->_model->getEncryptedSessionId());
    }

    public function testGetSessionIdQueryParam()
    {
        $this->assertEquals(
            Mage_Core_Model_Session_Abstract::SESSION_ID_QUERY_PARAM,
            $this->_model->getSessionIdQueryParam()
        );
    }

    public function testSetGetSkipSessionIdFlag()
    {
        $this->assertFalse($this->_model->getSkipSessionIdFlag());
        $this->_model->setSkipSessionIdFlag(true);
        $this->assertTrue($this->_model->getSkipSessionIdFlag());
    }


    public function testGetSessionIdForHost()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_model->init('test');
        $this->assertEmpty($this->_model->getSessionIdForHost('localhost'));
        $this->assertNotEmpty($this->_model->getSessionIdForHost('test'));
    }

    public function testIsValidForHost()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_model->init('test');
        $this->assertFalse($this->_model->isValidForHost('test.com'));
        $this->assertTrue($this->_model->isValidForHost('localhost'));
    }

    public function testGetSessionSaveMethod()
    {
        $this->assertEquals('files', $this->_model->getSessionSaveMethod());
    }

    public function testGetSessionSavePath()
    {
        $this->assertEquals(Mage::getBaseDir('session'), $this->_model->getSessionSavePath());
    }
}
