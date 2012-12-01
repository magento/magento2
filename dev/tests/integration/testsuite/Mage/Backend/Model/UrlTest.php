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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Model_Url.
 */
class Mage_Backend_Model_UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Url
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Backend_Model_Url');
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @covers Mage_Backend_Model_Url::getSecure
     */
    public function testGetSecure()
    {
        Mage::app()->getStore()->setConfig('web/secure/use_in_adminhtml', true);
        $this->assertTrue($this->_model->getSecure());

        Mage::app()->getStore()->setConfig('web/secure/use_in_adminhtml', false);
        $this->assertFalse($this->_model->getSecure());

        $this->_model->setData('secure_is_forced', true);
        $this->_model->setData('secure', true);
        $this->assertTrue($this->_model->getSecure());

        $this->_model->setData('secure', false);
        $this->assertFalse($this->_model->getSecure());
    }

    /**
     * @covers Mage_Backend_Model_Url::getSecure
     */
    public function testSetRouteParams()
    {
        $this->_model->setRouteParams(array('_nosecret' => 'any_value'));
        $this->assertTrue($this->_model->getNoSecret());

        $this->_model->setRouteParams(array());
        $this->assertFalse($this->_model->getNoSecret());
    }

    /**
     * App isolation is enabled to protect next tests from polluted registry by getUrl()
     *
     * @covers Mage_Backend_Model_Url::getSecure
     * @magentoConfigFixture admin/routers/adminhtml/args/frontName admin
     * @magentoAppIsolation enabled
     */
    public function testGetUrl()
    {
        $url = $this->_model->getUrl('adminhtml/auth/login');
        $this->assertContains('admin/auth/login/key/', $url);
    }

    /**
     * @param string $routeName
     * @param string $controller
     * @param string $action
     * @param string $expectedHash
     * @magentoConfigFixture global/helpers/core/encryption_model Mage_Core_Model_Encryption
     * @dataProvider getSecretKeyDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetSecretKey($routeName, $controller, $action, $expectedHash)
    {
        /** @var $request Mage_Core_Controller_Request_Http */
        $request = Mage::getModel('Mage_Core_Controller_Request_Http');
        $request->setControllerName('default_controller')
            ->setActionName('default_action')
            ->setRouteName('default_router');

        $this->_model->setRequest($request);
        Mage::getSingleton('Mage_Core_Model_Session')->setData('_form_key', 'salt');
        $this->assertEquals($expectedHash, $this->_model->getSecretKey($routeName, $controller, $action));
    }

    /**
     * @return array
     */
    public function getSecretKeyDataProvider()
    {
        return array(
            array('', '', '', '6f1957ed8fd24547bf3c2e75e97e965b'),
            array('', '', 'action', 'b7b02c691d8b36cd4c85dbb06fb78d63'),
            array('', 'controller', '', '8893bfa4185d5704449ab42b8b246e40'),
            array('', 'controller', 'action', '88b2bcff0469c6105cca241f76d0f5da'),
            array('adminhtml', '', '', '21c4e3709e616e6efb9d63a38184b8cc'),
            array('adminhtml', '', 'action', 'c0899247e4d9312541d06f44577b44ae'),
            array('adminhtml', 'controller', '', '39ee521775d46fc245d6791ca5cf1951'),
            array('adminhtml', 'controller', 'action', '6c9c1edd5bc6415506cab1ae9f11f581'),
        );
        // md5('controlleractionsalt') .
    }

    /**
     * @magentoConfigFixture global/helpers/core/encryption_model Mage_Core_Model_Encryption
     * @magentoAppIsolation enabled
     */
    public function testGetSecretKeyForwarded()
    {
        /** @var $request Mage_Core_Controller_Request_Http */
        $request = Mage::getModel('Mage_Core_Controller_Request_Http');
        $request->setControllerName('controller')->setActionName('action');
        $request->initForward()->setControllerName(uniqid())->setActionName(uniqid());
        $this->_model->setRequest($request);
        Mage::getSingleton('Mage_Core_Model_Session')->setData('_form_key', 'salt');
        $this->assertEquals('c36d05473b54f437889608cbe8d50339', $this->_model->getSecretKey());
    }

    public function testUseSecretKey()
    {
        $this->_model->setNoSecret(true);
        $this->assertFalse($this->_model->useSecretKey());

        $this->_model->setNoSecret(false);
        $this->assertTrue($this->_model->useSecretKey());
    }
}
