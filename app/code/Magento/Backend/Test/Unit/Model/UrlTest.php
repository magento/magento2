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
    protected $_model;

    /**
     * @var string
     */
    protected $_areaFrontName = 'backendArea';

    /**
     * @var MockObject
     */
    protected $_menuMock;

    /**
     * @var MockObject
     */
    protected $_formKey;

    /**
     * @var MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $_menuConfigMock;

    /**
     * @var MockObject
     */
    protected $_backendHelperMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var MockObject
     */
    protected $_authSessionMock;

    /**
     * @var MockObject
     */
    protected $routeParamsResolverFactoryMock;

    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_menuMock = $this->getMockBuilder(Menu::class)
            ->addMethods(['getFirstAvailableChild'])
            ->onlyMethods(['get', 'getFirstAvailable'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_menuConfigMock = $this->createMock(Config::class);
        $this->_menuConfigMock->expects($this->any())->method('getMenu')->willReturn($this->_menuMock);

        $this->_formKey = $this->createPartialMock(FormKey::class, ['getFormKey']);
        $this->_formKey->expects($this->any())->method('getFormKey')->willReturn('salt');

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

        $this->_menuMock->expects(
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
            $this->_areaFrontName
        );
        $this->_scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->_scopeConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            Url::XML_PATH_STARTUP_MENU_ITEM
        )->willReturn(
            'Magento_Backend::system_acl_roles'
        );

        $this->_authSessionMock = $this->createMock(Session::class);
        $this->_encryptor = $this->createPartialMock(Encryptor::class, ['getHash']);
        $this->_encryptor->expects($this->any())
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
            ->setMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->_model = $objectManager->getObject(
            Url::class,
            [
                'scopeConfig' => $this->_scopeConfigMock,
                'backendHelper' => $helperMock,
                'formKey' => $this->_formKey,
                'menuConfig' => $this->_menuConfigMock,
                'authSession' => $this->_authSessionMock,
                'encryptor' => $this->_encryptor,
                'routeParamsResolverFactory' => $this->routeParamsResolverFactoryMock,
                'hostChecker' => $hostCheckerMock,
                'serializer' => $this->serializerMock
            ]
        );
        $this->_requestMock = $this->createMock(Http::class);
        $this->_model->setRequest($this->_requestMock);
    }

    public function testFindFirstAvailableMenuDenied()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('setHasAvailableResources')->with(false);
        $mockSession = $this->getMockBuilder(Session::class)
            ->addMethods(['getUser'])
            ->onlyMethods(['isAllowed'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->expects($this->any())->method('getUser')->willReturn($user);

        $this->_model->setSession($mockSession);

        $this->_menuMock->expects($this->any())->method('getFirstAvailableChild')->willReturn(null);

        $this->assertEquals('*/denied', $this->_model->findFirstAvailableMenu());
    }

    public function testFindFirstAvailableMenu()
    {
        $user = $this->createMock(User::class);
        $mockSession = $this->getMockBuilder(Session::class)
            ->addMethods(['getUser'])
            ->onlyMethods(['isAllowed'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->expects($this->any())->method('getUser')->willReturn($user);

        $this->_model->setSession($mockSession);

        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())->method('getAction')->willReturn('adminhtml/user');
        $this->_menuMock->expects($this->any())->method('getFirstAvailable')->willReturn($itemMock);

        $this->assertEquals('adminhtml/user', $this->_model->findFirstAvailableMenu());
    }

    public function testGetStartupPageUrl()
    {
        $this->assertEquals('adminhtml/user_role', (string)$this->_model->getStartupPageUrl());
    }

    public function testGetAreaFrontName()
    {
        $helperMock = $this->createMock(Data::class);
        $helperMock->expects(
            $this->once()
        )->method(
            'getAreaFrontName'
        )->willReturn(
            $this->_areaFrontName
        );

        $helper = new ObjectManager($this);
        $urlModel = $helper->getObject(
            Url::class,
            [
                'backendHelper' => $helperMock,
                'authSession' => $this->_authSessionMock,
                'routeParamsResolverFactory' => $this->routeParamsResolverFactoryMock
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

        $keyWithRouteName = $this->_model->getSecretKey($routeName, $controllerName, $actionName);
        $keyWithoutRouteName = $this->_model->getSecretKey(null, $controllerName, $actionName);
        $keyDummyRouteName = $this->_model->getSecretKey('dummy', $controllerName, $actionName);

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

        $keyWithRouteName1 = $this->_model->getSecretKey($routeName, $controllerName, $actionName);
        $keyWithRouteName2 = $this->_model->getSecretKey($routeName, $controllerName, $actionName);

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

        $keyFromParams = $this->_model->getSecretKey($routeName, $controllerName, $actionName);

        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getBeforeForwardInfo'
        )->willReturn(
            null
        );
        $this->_requestMock->expects($this->once())->method('getRouteName')->willReturn($routeName);
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getControllerName'
        )->willReturn(
            $controllerName
        );
        $this->_requestMock->expects($this->once())->method('getActionName')->willReturn($actionName);
        $this->_model->setRequest($this->_requestMock);

        $keyFromRequest = $this->_model->getSecretKey();
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

        $keyFromParams = $this->_model->getSecretKey($routeName, $controllerName, $actionName);

        $this->_requestMock->expects(
            $this->at(0)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'route_name'
        )->willReturn(
            'adminhtml'
        );

        $this->_requestMock->expects(
            $this->at(1)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'route_name'
        )->willReturn(
            'adminhtml'
        );

        $this->_requestMock->expects(
            $this->at(2)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'controller_name'
        )->willReturn(
            'catalog'
        );

        $this->_requestMock->expects(
            $this->at(3)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'controller_name'
        )->willReturn(
            'catalog'
        );

        $this->_requestMock->expects(
            $this->at(4)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'action_name'
        )->willReturn(
            'index'
        );

        $this->_requestMock->expects(
            $this->at(5)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'action_name'
        )->willReturn(
            'index'
        );

        $this->_model->setRequest($this->_requestMock);
        $keyFromRequest = $this->_model->getSecretKey();
        $this->assertEquals($keyFromParams, $keyFromRequest);
    }

    public function testGetUrlWithUrlInRoutePath()
    {
        $routePath = 'https://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor';
        static::assertEquals($routePath, $this->_model->getUrl($routePath));
    }
}
