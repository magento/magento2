<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\ParamEncoder;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Test class for \Magento\Backend\Model\UrlInterface.
 *
 * @magentoAppArea adminhtml
 */
class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    protected $_model;

    protected function setUp()
    {
        $this->request = Bootstrap::getObjectManager()->get(RequestInterface::class);
        $this->_model = Bootstrap::getObjectManager()->create(UrlInterface::class);
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

        $routeParams = [
            '_escape_params' => false,
            'param1' => 'a1=='
        ];
        $url = $this->_model->getUrl('path', $routeParams);
        $this->assertContains('/param1/a1==/', $url);

        $this->request->setParams(['param2' => 'a2==']);
        $routeParams = [
            '_current' => true,
            '_escape_params' => false,
        ];
        $url = $this->_model->getUrl('path', $routeParams);
        $this->assertContains('/param2/a2==/', $url);

        /** @var ParamEncoder $paramEncoder */
        $paramEncoder = Bootstrap::getObjectManager()->get(ParamEncoder::class);
        $routeParams = [
            '_escape_params' => true,
            'param3' => 'a3=='
        ];
        $url = $this->_model->getUrl('path', $routeParams);
        $this->assertContains('/param3/' . $paramEncoder->encode('a3==') . '/', $url);

        $this->request->setParams(['param4' => 'a4==']);
        $routeParams = [
            '_current' => true,
            '_escape_params' => true,
        ];
        $url = $this->_model->getUrl('path', $routeParams);
        $this->assertContains('/param4/' . $paramEncoder->encode('a4==') . '/', $url);

        $url = $this->_model->getUrl('route/controller/action/id/100');
        $this->assertContains('id/100', $url);
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
        $this->request->setControllerName(
            'default_controller'
        )->setActionName(
            'default_action'
        )->setRouteName(
            'default_router'
        );

        $this->_model->setRequest($this->request);
        Bootstrap::getObjectManager()->get(
            SessionManagerInterface::class
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
        /** @var $encryptor EncryptorInterface */
        $encryptor = Bootstrap::getObjectManager()->get(EncryptorInterface::class);

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
        /** @var $encryptor EncryptorInterface */
        $encryptor = Bootstrap::getObjectManager()->get(EncryptorInterface::class);

        $this->request->setControllerName('controller')->setActionName('action');
        $this->request->initForward()->setControllerName(uniqid())->setActionName(uniqid());
        $this->_model->setRequest($this->request);
        Bootstrap::getObjectManager()->get(
            SessionManagerInterface::class
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
