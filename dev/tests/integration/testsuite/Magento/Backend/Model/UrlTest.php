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
namespace Magento\Backend\Model;

/**
 * Test class for \Magento\Backend\Model\UrlInterface.
 *
 * @magentoAppArea adminhtml
 */
class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\UrlInterface'
        );
    }

    /**
     * App isolation is enabled to protect next tests from polluted registry by getUrl()
     *
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
        /** @var $request \Magento\Framework\App\RequestInterface */
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\App\RequestInterface');
        $request->setControllerName(
            'default_controller'
        )->setActionName(
            'default_action'
        )->setRouteName(
            'default_router'
        );

        $this->_model->setRequest($request);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Session\SessionManagerInterface'
        )->setData(
            '_form_key',
            'salt'
        );
        $this->assertEquals($expectedHash, $this->_model->getSecretKey($routeName, $controller, $action));
    }

    /**
     * @return array
     */
    public function getSecretKeyDataProvider()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $encryptor \Magento\Framework\Encryption\EncryptorInterface */
        $encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');

        return array(
            array(
                '',
                '',
                '',
                $encryptor->getHash('default_router' . 'default_controller' . 'default_action' . 'salt')
            ),
            array('', '', 'action', $encryptor->getHash('default_router' . 'default_controller' . 'action' . 'salt')),
            array(
                '',
                'controller',
                '',
                $encryptor->getHash('default_router' . 'controller' . 'default_action' . 'salt')
            ),
            array(
                '',
                'controller',
                'action',
                $encryptor->getHash('default_router' . 'controller' . 'action' . 'salt')
            ),
            array(
                'adminhtml',
                '',
                '',
                $encryptor->getHash('adminhtml' . 'default_controller' . 'default_action' . 'salt')
            ),
            array(
                'adminhtml',
                '',
                'action',
                $encryptor->getHash('adminhtml' . 'default_controller' . 'action' . 'salt')
            ),
            array(
                'adminhtml',
                'controller',
                '',
                $encryptor->getHash('adminhtml' . 'controller' . 'default_action' . 'salt')
            ),
            array(
                'adminhtml',
                'controller',
                'action',
                $encryptor->getHash('adminhtml' . 'controller' . 'action' . 'salt')
            )
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetSecretKeyForwarded()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $encryptor \Magento\Framework\Encryption\EncryptorInterface */
        $encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');

        /** @var $request \Magento\Framework\App\Request\Http */
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\App\RequestInterface');
        $request->setControllerName('controller')->setActionName('action');
        $request->initForward()->setControllerName(uniqid())->setActionName(uniqid());
        $this->_model->setRequest($request);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Session\SessionManagerInterface'
        )->setData(
            '_form_key',
            'salt'
        );
        $this->assertEquals($encryptor->getHash('controller' . 'action' . 'salt'), $this->_model->getSecretKey());
    }

    public function testUseSecretKey()
    {
        $this->_model->setNoSecret(true);
        $this->assertFalse($this->_model->useSecretKey());

        $this->_model->setNoSecret(false);
        $this->assertTrue($this->_model->useSecretKey());
    }
}
