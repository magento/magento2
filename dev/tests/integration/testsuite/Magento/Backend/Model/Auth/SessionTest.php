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
namespace Magento\Backend\Model\Auth;

/**
 * @magentoAppArea adminhtml
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $this->_auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Auth'
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Auth\Session'
        );
        $this->_auth->setAuthStorage($this->_model);
    }

    protected function tearDown()
    {
        $this->_model = null;
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            null
        );
    }

    /**
     * Disabled form security in order to prevent exit from the app
     * @magentoAdminConfigFixture admin/security/session_lifetime 100
     */
    public function testIsLoggedIn()
    {
        $this->_auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->assertTrue($this->_model->isLoggedIn());

        $this->_model->setUpdatedAt(time() - 101);
        $this->assertFalse($this->_model->isLoggedIn());
    }

    /**
     * Disabled form security in order to prevent exit from the app
     * @magentoConfigFixture current_store admin/security/session_lifetime 59
     */
    public function testIsLoggedInWithIgnoredLifetime()
    {
        $this->_auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->assertTrue($this->_model->isLoggedIn());

        $this->_model->setUpdatedAt(time() - 101);
        $this->assertTrue($this->_model->isLoggedIn());
    }
}
