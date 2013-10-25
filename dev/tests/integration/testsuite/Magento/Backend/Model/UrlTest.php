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

namespace Magento\Backend\Model;

/**
 * Test class for \Magento\Backend\Model\Url.
 *
 * @magentoAppArea adminhtml
 */
class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Backend\Model\Url');
    }

    /**
     * @covers \Magento\Backend\Model\Url::getSecure
     */
    public function testIsSecure()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
            ->getStore()->setConfig('web/secure/use_in_adminhtml', true);
        $this->assertTrue($this->_model->isSecure());

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
            ->getStore()->setConfig('web/secure/use_in_adminhtml', false);
        $this->assertFalse($this->_model->isSecure());

        $this->_model->setData('secure_is_forced', true);
        $this->_model->setData('secure', true);
        $this->assertTrue($this->_model->isSecure());

        $this->_model->setData('secure', false);
        $this->assertFalse($this->_model->isSecure());
    }

    /**
     * @covers \Magento\Backend\Model\Url::getSecure
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
     * @covers \Magento\Backend\Model\Url::getSecure
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
     * @dataProvider getSecretKeyDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetSecretKey($routeName, $controller, $action, $expectedHash)
    {
        /** @var $request \Magento\App\RequestInterface */
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\App\RequestInterface');
        $request->setControllerName('default_controller')
            ->setActionName('default_action')
            ->setRouteName('default_router');

        $this->_model->setRequest($request);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Session')
            ->setData('_form_key', 'salt');
        $this->assertEquals($expectedHash, $this->_model->getSecretKey($routeName, $controller, $action));
    }

    /**
     * @return array
     */
    public function getSecretKeyDataProvider()
    {
        /** @var $helper \Magento\Core\Helper\Data */
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Helper\Data');
        return array(
            array('', '', '',
                $helper->getHash('default_router' . 'default_controller' . 'default_action' . 'salt')),
            array('', '', 'action',
                $helper->getHash('default_router' . 'default_controller' . 'action' . 'salt')),
            array('', 'controller', '',
                $helper->getHash('default_router' . 'controller' . 'default_action' . 'salt')),
            array('', 'controller', 'action',
                $helper->getHash('default_router' . 'controller' . 'action' . 'salt')),
            array('adminhtml', '', '',
                $helper->getHash('adminhtml' . 'default_controller' . 'default_action' . 'salt')),
            array('adminhtml', '', 'action',
                $helper->getHash('adminhtml' . 'default_controller' . 'action' . 'salt')),
            array('adminhtml', 'controller', '',
                $helper->getHash('adminhtml' . 'controller' . 'default_action' . 'salt')),
            array('adminhtml', 'controller', 'action',
                $helper->getHash('adminhtml' . 'controller' . 'action' . 'salt')),
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetSecretKeyForwarded()
    {
        /** @var $helper \Magento\Core\Helper\Data */
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Helper\Data');
        /** @var $request \Magento\App\RequestInterface */
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\App\RequestInterface');
        $request->setControllerName('controller')->setActionName('action');
        $request->initForward()->setControllerName(uniqid())->setActionName(uniqid());
        $this->_model->setRequest($request);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Session')
            ->setData('_form_key', 'salt');
        $this->assertEquals(
            $helper->getHash('controller' . 'action' . 'salt'),
            $this->_model->getSecretKey()
        );
    }

    public function testUseSecretKey()
    {
        $this->_model->setNoSecret(true);
        $this->assertFalse($this->_model->useSecretKey());

        $this->_model->setNoSecret(false);
        $this->assertTrue($this->_model->useSecretKey());
    }
}
