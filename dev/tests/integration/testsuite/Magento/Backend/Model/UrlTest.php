<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Escaper;

/**
 * Test class for \Magento\Backend\Model\UrlInterface.
 *
 * @magentoAppArea adminhtml
 */
class UrlTest extends \PHPUnit\Framework\TestCase
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
     * App isolation is enabled to protect next tests from polluted registry by getUrl().
     *
     * @param string $routePath
     * @param array $requestParams
     * @param string $expectedResult
     * @param array|null $routeParams
     * @dataProvider getUrlDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetUrl(
        string $routePath,
        array $requestParams,
        string $expectedResult,
        array $routeParams = null
    ) {
        $this->request->setParams($requestParams);
        $url = $this->_model->getUrl($routePath, $routeParams);
        $this->assertContains($expectedResult, $url);
    }

    /**
     * Data provider for getUrl method.
     *
     * @return array
     */
    public function getUrlDataProvider()
    {
        /** @var $escaper Escaper */
        $escaper = Bootstrap::getObjectManager()->get(Escaper::class);

        return [
            [
                'routePath' => 'adminhtml/auth/login',
                'requestParams' => [],
                'expectedResult'=> 'admin/auth/login/key/',
            ],
            [
                'routePath' => 'adminhtml/auth/login',
                'requestParams' => [],
                'expectedResult'=> '/param1/a1==/',
                'routeParams' => [
                    '_escape_params' => false,
                    'param1' => 'a1==',
                ],
            ],
            [
                'routePath' => 'adminhtml/auth/login',
                'requestParams' => [],
                'expectedResult'=> '/param1/a1==/',
                'routeParams' => [
                    '_escape_params' => false,
                    'param1' => 'a1==',
                ],
            ],
            [
                'routePath' => 'adminhtml/auth/login',
                'requestParams' => ['param2' => 'a2=='],
                'expectedResult'=> '/param2/a2==/',
                'routeParams' => [
                    '_current' => true,
                    '_escape_params' => false,
                ],
            ],
            [
                'routePath' => 'adminhtml/auth/login',
                'requestParams' => [],
                'expectedResult' => '/param3/' . $escaper->encodeUrlParam('a3==') . '/',
                'routeParams' => [
                    '_escape_params' => true,
                    'param3' => 'a3=='
                ],
            ],
            [
                'routePath' => 'adminhtml/auth/login',
                'requestParams' => ['param4' => 'a4=='],
                'expectedResult' => '/param4/' . $escaper->encodeUrlParam('a4==') . '/',
                'routeParams' => [
                    '_current' => true,
                    '_escape_params' => true,
                ],
            ],
            [
                'routePath' => 'route/controller/action/id/100',
                'requestParams' => [],
                'expectedResult' => 'id/100',
            ],
        ];
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
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Session\SessionManagerInterface::class
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
