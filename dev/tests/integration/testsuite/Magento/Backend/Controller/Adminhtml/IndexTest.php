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
 * @package     Magento_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Backend\Controller\Adminhtml;

/**
 * Test class for \Magento\Backend\Controller\Adminhtml\Index.
 *
 * @magentoAppArea adminhtml
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * Performs user login
     */
    protected  function _login()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Backend\Model\Url')
            ->turnOffSecretKey();
        $this->_auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Backend\Model\Auth');
        $this->_auth->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME, \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD);
    }

    /**
     * Performs user logout
     */
    protected function _logout()
    {
        $this->_auth->logout();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Backend\Model\Url')->turnOnSecretKey();
    }

    /**
     * Check not logged state
     * @covers \Magento\Backend\Controller\Adminhtml\Index::indexAction
     */
    public function testNotLoggedIndexAction()
    {
        $this->dispatch('backend/admin/index/index');
        $this->assertFalse($this->getResponse()->isRedirect());

        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('form#login-form input#username[type=text]', true, $body);
        $this->assertSelectCount('form#login-form input#login[type=password]', true, $body);
    }

    /**
     * Check logged state
     * @covers \Magento\Backend\Controller\Adminhtml\Index::indexAction
     * @magentoDbIsolation enabled
     */
    public function testLoggedIndexAction()
    {
        $this->_login();
        $this->dispatch('backend/admin/index/index');
        $this->assertRedirect();
        $this->_logout();
    }
}
