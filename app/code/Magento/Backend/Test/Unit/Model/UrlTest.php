<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Config;
use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\HostChecker;
use Magento\Framework\Url\RouteParamsResolver;
use Magento\Framework\Url\RouteParamsResolverFactory;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlTest extends TestCase
{
    /**
     * @var Url
     */
    protected $model;

    /**
     * @var string
     */
    protected $areaFrontName = 'backendArea';

    /**
     * @var MockObject
     */
    protected $menuMock;

    /**
     * @var MockObject
     */
    protected $formKey;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $menuConfigMock;

    /**
     * @var MockObject
     */
    protected $backendHelperMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $authSessionMock;

    /**
     * @var MockObject
     */
    protected $routeParamsResolverFactoryMock;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->menuMock = $this->getMockBuilder(Menu::class)
            ->addMethods(['getFirstAvailableChild'])
            ->onlyMethods(['get', 'getFirstAvailable'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->menuConfigMock = $this->createMock(Config::class);
        $this->menuConfigMock->expects($this->any())->method('getMenu')->willReturn($this->menuMock);

        $this->formKey = $this->createPartialMock(FormKey::class, ['getFormKey']);
        $this->formKey->expects($this->any())->method('getFormKey')->willReturn('salt');

        $mockItem = $this->createMock(Item::class);
        $mockItem->expects($this->any())->method('isDisabled')->willReturn(false);
        $mockItem->expects($this->any())->method('isAllowed')->willReturn(true);
        $mockItem->expects(
            $this->any()
        )->method(
            'getId'
        )->willReturn(
            'Magento_Backend::system_acl_roles'
        );
        $mockItem->expects($this->any())->method('getAction')->willReturn('adminhtml/user_role');

        $this->menuMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'Magento_Backend::system_acl_roles'
        )->willReturn(
            $mockItem
        );

        $helperMock = $this->createMock(Data::class);
        $helperMock->expects(
            $this->any()
        )->method(
            'getAreaFrontName'
        )->willReturn(
            $this->areaFrontName
        );
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->scopeConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            Url::XML_PATH_STARTUP_MENU_ITEM
        )->willReturn(
            'Magento_Backend::system_acl_roles'
        );

        $this->authSessionMock = $this->createMock(Session::class);
        $this->encryptor = $this->createPartialMock(Encryptor::class, ['getHash']);
        $this->encryptor->expects($this->any())
            ->method('getHash')
            ->willReturnArgument(0);
        $routeParamsResolver = $this->createMock(RouteParamsResolver::class);
        $this->routeParamsResolverFactoryMock = $this->createMock(
            RouteParamsResolverFactory::class
        );
        $this->routeParamsResolverFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($routeParamsResolver);
        /** @var HostChecker|MockObject $hostCheckerMock */
        $hostCheckerMock = $this->createMock(HostChecker::class);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->onlyMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->model = $objectManager->getObject(
            Url::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'backendHelper' => $helperMock,
                'formKey' => $this->formKey,
                'menuConfig' => $this->menuConfigMock,
                'authSession' => $this->authSessionMock,
                'encryptor' => $this->encryptor,
                'routeParamsResolverFactory' => $this->routeParamsResolverFactoryMock,
                'hostChecker' => $hostCheckerMock,
                'serializer' => $this->serializerMock
            ]
        );
        $this->requestMock = $this->createMock(Http::class);
        $this->model->setRequest($this->requestMock);
    }

    /**
     * @return void
     */
    public function testFindFirstAvailableMenuDenied(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('setHasAvailableResources')->with(false);
        $mockSession = $this->getMockBuilder(Session::class)
            ->addMethods(['getUser'])
            ->onlyMethods(['isAllowed'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->expects($this->any())->method('getUser')->willReturn($user);

        $this->model->setSession($mockSession);

        $this->menuMock->expects($this->any())->method('getFirstAvailableChild')->willReturn(null);

        $this->assertEquals('*/denied', $this->model->findFirstAvailableMenu());
    }

    /**
     * @return void
     */
    public function testFindFirstAvailableMenu(): void
    {
        $user = $this->createMock(User::class);
        $mockSession = $this->getMockBuilder(Session::class)
            ->addMethods(['getUser'])
            ->onlyMethods(['isAllowed'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->expects($this->any())->method('getUser')->willReturn($user);

        $this->model->setSession($mockSession);

        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())->method('getAction')->willReturn('adminhtml/user');
        $this->menuMock->expects($this->any())->method('getFirstAvailable')->willReturn($itemMock);

        $this->assertEquals('adminhtml/user', $this->model->findFirstAvailableMenu());
    }

    /**
     * @return void
     */
    public function testGetStartupPageUrl(): void
    {
        $this->assertEquals('adminhtml/user_role', (string)$this->model->getStartupPageUrl());
    }

    /**
     * @return void
     */
    public function testGetAreaFrontName(): void
    {
        $helperMock = $this->createMock(Data::class);
        $helperMock->expects(
            $this->once()
        )->method(
            'getAreaFrontName'
        )->willReturn(
            $this->areaFrontName
        );

        $helper = new ObjectManager($this);
        $urlModel = $helper->getObject(
            Url::class,
            [
                'backendHelper' => $helperMock,
                'authSession' => $this->authSessionMock,
                'routeParamsResolverFactory' => $this->routeParamsResolverFactoryMock
            ]
        );
        $urlModel->getAreaFrontName();
    }

    /**
     * Check that secret key generation is based on usage of routeName passed as method param
     * Params are not equals.
     *
     * @return void
     */
    public function testGetSecretKeyGenerationWithRouteNameAsParamNotEquals(): void
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
     * Params are equals.
     *
     * @return void
     */
    public function testGetSecretKeyGenerationWithRouteNameAsParamEquals(): void
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyWithRouteName1 = $this->model->getSecretKey($routeName, $controllerName, $actionName);
        $keyWithRouteName2 = $this->model->getSecretKey($routeName, $controllerName, $actionName);

        $this->assertEquals($keyWithRouteName1, $keyWithRouteName2);
    }

    /**
     * Check that secret key generation is based on usage of routeName extracted from request.
     *
     * @return void
     */
    public function testGetSecretKeyGenerationWithRouteNameInRequest(): void
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyFromParams = $this->model->getSecretKey($routeName, $controllerName, $actionName);

        $this->requestMock->expects(
            $this->exactly(3)
        )->method(
            'getBeforeForwardInfo'
        )->willReturn(
            null
        );
        $this->requestMock->expects($this->once())->method('getRouteName')->willReturn($routeName);
        $this->requestMock->expects(
            $this->once()
        )->method(
            'getControllerName'
        )->willReturn(
            $controllerName
        );
        $this->requestMock->expects($this->once())->method('getActionName')->willReturn($actionName);
        $this->model->setRequest($this->requestMock);

        $keyFromRequest = $this->model->getSecretKey();
        $this->assertEquals($keyFromParams, $keyFromRequest);
    }

    /**
     * Check that secret key generation is based on usage of routeName extracted from request Forward info.
     *
     * @return void
     */
    public function testGetSecretKeyGenerationWithRouteNameInForwardInfo(): void
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyFromParams = $this->model->getSecretKey($routeName, $controllerName, $actionName);

        $this->requestMock
            ->method('getBeforeForwardInfo')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['route_name'] => 'adminhtml',
                ['controller_name'] => 'catalog',
                ['action_name'] => 'index'
            });

        $this->model->setRequest($this->requestMock);
        $keyFromRequest = $this->model->getSecretKey();
        $this->assertEquals($keyFromParams, $keyFromRequest);
    }

    /**
     * @return void
     */
    public function testGetUrlWithUrlInRoutePath(): void
    {
        $routePath = 'https://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor';
        static::assertEquals($routePath, $this->model->getUrl($routePath));
    }
}
