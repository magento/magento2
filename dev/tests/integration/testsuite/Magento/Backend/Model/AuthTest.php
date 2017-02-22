<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

use Magento\Framework\Exception\AuthenticationException;

/**
 * Test class for \Magento\Backend\Model\Auth.
 *
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class AuthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();

        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Backend\Model\Auth');
    }

    /**
     * @dataProvider getLoginDataProvider
     * @param string $userName
     * @param string $password
     * @expectedException \Magento\Framework\Exception\AuthenticationException
     */
    public function testLoginFailed($userName, $password)
    {
        $this->_model->login($userName, $password);
    }

    public function getLoginDataProvider()
    {
        return [
            'Invalid credentials' => ['not_exists', 'not_exists'],
            'Empty credentials' => ['', 'not_exists']
        ];
    }

    public function testSetGetAuthStorage()
    {
        // by default \Magento\Backend\Model\Auth\Session class will instantiate as a Authentication Storage
        $this->assertInstanceOf('Magento\Backend\Model\Auth\Session', $this->_model->getAuthStorage());

        $mockStorage = $this->getMock('Magento\Backend\Model\Auth\StorageInterface');
        $this->_model->setAuthStorage($mockStorage);
        $this->assertInstanceOf('Magento\Backend\Model\Auth\StorageInterface', $this->_model->getAuthStorage());

        $incorrectStorage = new \StdClass();
        try {
            $this->_model->setAuthStorage($incorrectStorage);
            $this->fail('Incorrect authentication storage setted.');
        } catch (AuthenticationException $e) {
            // in case of exception - Auth works correct
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function testGetCredentialStorageList()
    {
        $storage = $this->_model->getCredentialStorage();
        $this->assertInstanceOf('Magento\Backend\Model\Auth\Credential\StorageInterface', $storage);
    }

    public function testLoginSuccessful()
    {
        $this->_model->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->assertInstanceOf('Magento\Backend\Model\Auth\Credential\StorageInterface', $this->_model->getUser());
        $this->assertGreaterThan(time() - 10, $this->_model->getAuthStorage()->getUpdatedAt());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testLogout()
    {
        $this->_model->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->assertNotEmpty($this->_model->getAuthStorage()->getData());
        $this->_model->logout();
        $this->assertEmpty($this->_model->getAuthStorage()->getData());
    }

    /**
     * Disabled form security in order to prevent exit from the app
     * @magentoAdminConfigFixture admin/security/session_lifetime 100
     */
    public function testIsLoggedIn()
    {
        $this->_model->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
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
        $this->_model->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->assertTrue($this->_model->isLoggedIn());

        $this->_model->getAuthStorage()->setUpdatedAt(time() - 101);
        $this->assertTrue($this->_model->isLoggedIn());
    }

    public function testGetUser()
    {
        $this->_model->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $this->assertNotNull($this->_model->getUser());
        $this->assertGreaterThan(0, $this->_model->getUser()->getId());
        $this->assertInstanceOf('Magento\Backend\Model\Auth\Credential\StorageInterface', $this->_model->getUser());
    }
}
