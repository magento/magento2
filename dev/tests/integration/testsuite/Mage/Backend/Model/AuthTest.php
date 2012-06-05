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
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Model_Auth.
 *
 * @group module:Mage_Backend
 */
class Mage_Backend_Model_AuthTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Auth
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_Backend_Model_Auth();
    }

    /**
     * @expectedException Mage_Backend_Model_Auth_Exception
     */
    public function testLoginFailed()
    {
        $this->_model->login('not_exists', 'not_exists');
    }

    public function testSetGetAuthStorage()
    {
        // by default Mage_Backend_Model_Auth_Session class will instantiate as a Authentication Storage
        $this->assertInstanceOf('Mage_Backend_Model_Auth_Session', $this->_model->getAuthStorage());

        $mockStorage = $this->getMock('Mage_Backend_Model_Auth_StorageInterface');
        $this->_model->setAuthStorage($mockStorage);
        $this->assertInstanceOf('Mage_Backend_Model_Auth_StorageInterface', $this->_model->getAuthStorage());

        $incorrectStorage = new StdClass();
        try {
            $this->_model->setAuthStorage($incorrectStorage);
            $this->fail('Incorrect authentication storage setted.');
        } catch (Mage_Backend_Model_Auth_Exception $e) {
            // in case of exception - Auth works correct
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function testGetCredentialStorageList()
    {
        $storage = $this->_model->getCredentialStorage();
        $this->assertInstanceOf('Mage_Backend_Model_Auth_Credential_StorageInterface', $storage);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testLoginSuccessful()
    {
        $this->_model->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
        $this->assertInstanceOf('Mage_Backend_Model_Auth_Credential_StorageInterface', $this->_model->getUser());
        $this->assertGreaterThan(time() - 10, $this->_model->getAuthStorage()->getUpdatedAt());
    }

    public function testLogout()
    {
        $this->_model->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
        $this->assertNotEmpty($this->_model->getAuthStorage()->getData());
        $this->_model->getAuthStorage()
            ->getCookie()
            ->set($this->_model->getAuthStorage()->getSessionName(), 'session_id');
        $this->_model->logout();
        $this->assertEmpty($this->_model->getAuthStorage()->getData());
        $this->assertEmpty($this->_model->getAuthStorage()
            ->getCookie()
            ->get($this->_model->getAuthStorage()->getSessionName())
        );
    }

    /**
     * Disabled form security in order to prevent exit from the app
     * @magentoConfigFixture current_store admin/security/session_lifetime 100
     */
    public function testIsLoggedIn()
    {
        $this->_model->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
        $this->assertTrue($this->_model->isLoggedIn());

        $this->_model->getAuthStorage()->setUpdatedAt(time() - 101);
        $this->assertFalse($this->_model->isLoggedIn());
    }

    /**
     * Disabled form security in order to prevent exit from the app
     * @magentoConfigFixture current_store admin/security/session_lifetime 59
     */
    public function testIsLoggedInWithIgnoredLifetime()
    {
        $this->_model->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
        $this->assertTrue($this->_model->isLoggedIn());

        $this->_model->getAuthStorage()->setUpdatedAt(time() - 101);
        $this->assertTrue($this->_model->isLoggedIn());
    }

    public function testGetUser()
    {
        $this->_model->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);

        $this->assertNotNull($this->_model->getUser());
        $this->assertGreaterThan(0, $this->_model->getUser()->getId());
        $this->assertInstanceOf('Mage_Backend_Model_Auth_Credential_StorageInterface', $this->_model->getUser());
    }
}
