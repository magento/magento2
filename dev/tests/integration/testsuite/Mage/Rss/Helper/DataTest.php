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
 * @package     Mage_Rss
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Rss
 */
class Mage_Rss_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Rss_Helper_Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = new Mage_Rss_Helper_Data;
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAuthAdminLoggedIn()
    {
        $admin = new Varien_Object(array('id' => 1));
        $session = Mage::getSingleton('Mage_Rss_Model_Session');
        $session->setAdmin($admin);
        $this->assertEquals($admin, $this->_helper->authAdmin(''));
    }

    public function testAuthAdminNotLogged()
    {
        $this->markTestIncomplete('Incomplete until helper stops exiting script for non-logged user');
        $this->assertFalse($this->_helper->authAdmin(''));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAuthAdminLogin()
    {
        $_SERVER['PHP_AUTH_USER'] = Magento_Test_Bootstrap::ADMIN_NAME;
        $_SERVER['PHP_AUTH_PW'] = Magento_Test_Bootstrap::ADMIN_PASSWORD;
        $this->assertInstanceOf('Mage_Admin_Model_User', $this->_helper->authAdmin(''));

        $response = Mage::app()->getResponse();
        $code = $response->getHttpResponseCode();
        $this->assertFalse(($code >= 300) && ($code < 400));
    }
}
