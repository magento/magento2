<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Model;

/**
 * Test class for \Magento\Backend\Model\Url.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    private $model;

    /**
     * @var string
     */
    private $areaFrontName = 'backendArea';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $menuMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $formKey;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $menuConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $routeParamsResolverFactoryMock;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->menuMock = $this->getMock(
            \Magento\Backend\Model\Menu::class,
            [],
            [$this->getMock(\Psr\Log\LoggerInterface::class)]
        );

        $this->menuConfigMock = $this->getMock(\Magento\Backend\Model\Menu\Config::class, [], [], '', false);
        $this->menuConfigMock->expects($this->any())->method('getMenu')->will($this->returnValue($this->menuMock));

        $this->formKey = $this->getMock(\Magento\Framework\Data\Form\FormKey::class, ['getFormKey'], [], '', false);
        $this->formKey->expects($this->any())->method('getFormKey')->will($this->returnValue('salt'));

        $mockItem = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $mockItem->expects($this->any())->method('isDisabled')->willReturn(false);
        $mockItem->expects($this->any())->method('isAllowed')->willReturn(true);
        $mockItem->expects($this->any())->method('getId')->willReturn('Magento_Backend::system_acl_roles');
        $mockItem->expects($this->any())->method('getAction')->willReturn('adminhtml/user_role');

        $this->menuMock->expects($this->any())
            ->method('get')
            ->with('Magento_Backend::system_acl_roles')
            ->willReturn($mockItem);

        $helperMock = $this->getMock(\Magento\Backend\Helper\Data::class, [], [], '', false);
        $helperMock->expects($this->any())
            ->method('getAreaFrontName')
            ->willReturn($this->areaFrontName);
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(\Magento\Backend\Model\Url::XML_PATH_STARTUP_MENU_ITEM)
            ->willReturn('Magento_Backend::system_acl_roles');

        $this->authSessionMock = $this->getMock(
            \Magento\Backend\Model\Auth\Session::class,
            [],
            [],
            '',
            false,
            false
        );
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->encryptor = $this->getMock(\Magento\Framework\Encryption\Encryptor::class, ['getHash'], [], '', false);
        $this->encryptor->expects($this->any())->method('getHash')->willReturnArgument(0);
        $this->routeParamsResolverFactoryMock = $this->getMock(
            \Magento\Framework\Url\RouteParamsResolverFactory::class,
            [],
            [],
            '',
            false
        );
        $this->routeParamsResolverFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn(
                $this->getMock(\Magento\Framework\Url\RouteParamsResolver::class, [], [], '', false)
            );

        $this->model = $helper->getObject(
            \Magento\Backend\Model\Url::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'backendHelper' => $helperMock,
                'formKey' => $this->formKey,
                'menuConfig' => $this->menuConfigMock,
                'authSession' => $this->authSessionMock,
                'encryptor' => $this->encryptor,
                'routeParamsResolverFactory' => $this->routeParamsResolverFactoryMock,
            ]
        );
        $this->routeParamsResolverFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn(
                $this->getMock(\Magento\Framework\Url\RouteParamsResolver::class, [], [], '', false)
            );

        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->model->setRequest($this->requestMock);
    }

    public function testFindFirstAvailableMenuDenied()
    {
        $user = $this->getMock(\Magento\User\Model\User::class, [], [], '', false);
        $user->expects($this->once())->method('setHasAvailableResources')->with(false);
        $mockSession = $this->getMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['getUser', 'isAllowed'],
            [],
            '',
            false
        );
        $mockSession->expects($this->any())->method('getUser')->willReturn($user);

        $this->model->setSession($mockSession);

        $this->menuMock->expects($this->any())->method('getFirstAvailableChild')->willReturn(null);

        $this->assertEquals('*/*/denied', $this->model->findFirstAvailableMenu());
    }

    public function testFindFirstAvailableMenu()
    {
        $user = $this->getMock(\Magento\User\Model\User::class, [], [], '', false);
        $mockSession = $this->getMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['getUser', 'isAllowed'],
            [],
            '',
            false
        );

        $mockSession->expects($this->any())->method('getUser')->will($this->returnValue($user));

        $this->model->setSession($mockSession);

        $itemMock = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $itemMock->expects($this->once())->method('getAction')->will($this->returnValue('adminhtml/user'));
        $this->menuMock->expects($this->any())->method('getFirstAvailable')->will($this->returnValue($itemMock));

        $this->assertEquals('adminhtml/user', $this->model->findFirstAvailableMenu());
    }

    public function testGetStartupPageUrl()
    {
        $this->assertEquals('adminhtml/user_role', (string)$this->model->getStartupPageUrl());
    }

    public function testGetAreaFrontName()
    {
        $helperMock = $this->getMock(\Magento\Backend\Helper\Data::class, [], [], '', false);
        $helperMock->expects($this->once())
            ->method('getAreaFrontName')
            ->willReturn($this->areaFrontName);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $urlModel = $helper->getObject(
            \Magento\Backend\Model\Url::class,
            [
                'backendHelper' => $helperMock,
                'authSession' => $this->authSessionMock,
                'routeParamsResolverFactory' => $this->routeParamsResolverFactoryMock,
            ]
        );
        $urlModel->getAreaFrontName();
    }

    /**
     * Check that secret key generation is based on usage of routeName passed as method param
     * Params are not equals
     */
    public function testGetSecretKeyGenerationWithRouteNameAsParamNotEquals()
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyWithRouteName = $this->model->getSecretKey($routeName, $controllerName, $actionName);
        $keyWithoutRouteName = $this->model->getSecretKey(null, $controllerName, $actionName);
        $keyDummyRouteName = $this->model->getSecretKey('dummy', $controllerName, $actionName);

        $this->assertNotEquals($keyWithRouteName, $keyWithoutRouteName);
        $this->assertNotEquals($keyWithRouteName, $keyDummyRouteName);
    }

    /**
     * Check that secret key generation is based on usage of routeName passed as method param
     * Params are equals
     */
    public function testGetSecretKeyGenerationWithRouteNameAsParamEquals()
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyWithRouteName1 = $this->model->getSecretKey($routeName, $controllerName, $actionName);
        $keyWithRouteName2 = $this->model->getSecretKey($routeName, $controllerName, $actionName);

        $this->assertEquals($keyWithRouteName1, $keyWithRouteName2);
    }

    /**
     * Check that secret key generation is based on usage of routeName extracted from request
     */
    public function testGetSecretKeyGenerationWithRouteNameInRequest()
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyFromParams = $this->model->getSecretKey($routeName, $controllerName, $actionName);

        $this->requestMock->expects($this->exactly(3))->method('getBeforeForwardInfo')->willReturn(null);
        $this->requestMock->expects($this->once())->method('getRouteName')->willReturn($routeName);
        $this->requestMock->expects($this->once())->method('getControllerName')->willReturn($controllerName);
        $this->requestMock->expects($this->once())->method('getActionName')->willReturn($actionName);
        $this->model->setRequest($this->requestMock);

        $keyFromRequest = $this->model->getSecretKey();
        $this->assertEquals($keyFromParams, $keyFromRequest);
    }

    /**
     * Check that secret key generation is based on usage of routeName extracted from request Forward info
     */
    public function testGetSecretKeyGenerationWithRouteNameInForwardInfo()
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyFromParams = $this->model->getSecretKey($routeName, $controllerName, $actionName);

        $this->requestMock->expects($this->at(0))
            ->method('getBeforeForwardInfo')
            ->with('route_name')
            ->willReturn('adminhtml');
        $this->requestMock->expects($this->at(1))
            ->method('getBeforeForwardInfo')
            ->with('route_name')
            ->willReturn('adminhtml');
        $this->requestMock->expects($this->at(2))
            ->method('getBeforeForwardInfo')
            ->with('controller_name')
            ->willReturn('catalog');
        $this->requestMock->expects($this->at(3))
            ->method('getBeforeForwardInfo')
            ->with('controller_name')
            ->willReturn('catalog');
        $this->requestMock->expects($this->at(4))
            ->method('getBeforeForwardInfo')
            ->with('action_name')
            ->willReturn('index');
        $this->requestMock->expects($this->at(5))
            ->method('getBeforeForwardInfo')
            ->with('action_name')
            ->willReturn('index');

        $this->model->setRequest($this->requestMock);
        $keyFromRequest = $this->model->getSecretKey();
        $this->assertEquals($keyFromParams, $keyFromRequest);
    }

    public function testGetUrlWithUrlInRoutePath()
    {
        $routePath = 'https://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor';
        static::assertEquals($routePath, $this->model->getUrl($routePath));
    }
}
