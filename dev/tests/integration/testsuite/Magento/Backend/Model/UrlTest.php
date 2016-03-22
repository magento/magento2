<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

        return [
            [
                '',
                '',
                '',
                $encryptor->getHash('default_router' . 'default_controller' . 'default_action' . 'salt'),
            ],
            ['', '', 'action', $encryptor->getHash('default_router' . 'default_controller' . 'action' . 'salt')],
            [
                '',
                'controller',
                '',
                $encryptor->getHash('default_router' . 'controller' . 'default_action' . 'salt')
            ],
            [
                '',
                'controller',
                'action',
                $encryptor->getHash('default_router' . 'controller' . 'action' . 'salt')
            ],
            [
                'adminhtml',
                '',
                '',
                $encryptor->getHash('adminhtml' . 'default_controller' . 'default_action' . 'salt')
            ],
            [
                'adminhtml',
                '',
                'action',
                $encryptor->getHash('adminhtml' . 'default_controller' . 'action' . 'salt')
            ],
            [
                'adminhtml',
                'controller',
                '',
                $encryptor->getHash('adminhtml' . 'controller' . 'default_action' . 'salt')
            ],
            [
                'adminhtml',
                'controller',
                'action',
                $encryptor->getHash('adminhtml' . 'controller' . 'action' . 'salt')
            ]
        ];
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
