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
 * @category    Mage
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Controller_ActionAbstract.
 *
 */
class Mage_Backend_Controller_ActionAbstractTest extends Mage_Adminhtml_Utility_Controller
{
    /**
     * Check redirection to startup page for logged user
     * @magentoConfigFixture admin/routers/adminhtml/args/frontName admin
     * @magentoConfigFixture current_store admin/security/use_form_key 1
     */
    public function testPreDispatchWithEmptyUrlRedirectsToStartupPage()
    {
        $expected = Mage::getSingleton('Mage_Backend_Model_Url')->getUrl('adminhtml/dashboard');
        $this->dispatch('/admin');
        $this->assertRedirect($expected, self::MODE_START_WITH);
    }

    /**
     * Check login redirection
     *
     * @covers Mage_Backend_Controller_ActionAbstract::_initAuthentication
     * @magentoDataFixture emptyDataFixture
     */
    public function testInitAuthentication()
    {
        /**
         * Logout current session
         */
        $this->_auth->logout();

        $postLogin = array('login' => array(
            'username' => Magento_Test_Bootstrap::ADMIN_NAME,
            'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD
        ));

        $this->getRequest()->setPost($postLogin);
        $url = Mage::getSingleton('Mage_Backend_Model_Url')->getUrl('adminhtml/system_account/index');
        $this->dispatch($url);

        $expected = 'admin/system_account/index';
        $this->assertRedirect($expected, self::MODE_CONTAINS);
    }

    /**
     * Empty data fixture to provide support of transaction
     * @static
     *
     */
    public static function emptyDataFixture()
    {

    }
}
