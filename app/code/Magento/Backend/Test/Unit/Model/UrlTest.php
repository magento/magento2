<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\HostChecker;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 */
class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_areaFrontName = 'backendArea';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formKey;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $routeParamsResolverFactoryMock;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_menuMock = $this->createPartialMock(\Magento\Backend\Model\Menu::class, ['getFirstAvailableChild', 'get', 'getFirstAvailable']);

        $this->_menuConfigMock = $this->createMock(\Magento\Backend\Model\Menu\Config::class);
        $this->_menuConfigMock->expects($this->any())->method('getMenu')->will($this->returnValue($this->_menuMock));

        $this->_formKey = $this->createPartialMock(\Magento\Framework\Data\Form\FormKey::class, ['getFormKey']);
        $this->_formKey->expects($this->any())->method('getFormKey')->will($this->returnValue('salt'));

        $mockItem = $this->createMock(\Magento\Backend\Model\Menu\Item::class);
        $mockItem->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $mockItem->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $mockItem->expects(
            $this->any()
        )->method(
            'getId'
        )->will(
            $this->returnValue('Magento_Backend::system_acl_roles')
        );
        $mockItem->expects($this->any())->method('getAction')->will($this->returnValue('adminhtml/user_role'));

        $this->_menuMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            $this->equalTo('Magento_Backend::system_acl_roles')
        )->will(
            $this->returnValue($mockItem)
        );

        $helperMock = $this->createMock(\Magento\Backend\Helper\Data::class);
        $helperMock->expects(
            $this->any()
        )->method(
            'getAreaFrontName'
        )->will(
            $this->returnValue($this->_areaFrontName)
        );
        $this->_scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_scopeConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            \Magento\Backend\Model\Url::XML_PATH_STARTUP_MENU_ITEM
        )->will(
            $this->returnValue('Magento_Backend::system_acl_roles')
        );

        $this->_authSessionMock = $this->createMock(\Magento\Backend\Model\Auth\Session::class);
        $this->_encryptor = $this->createPartialMock(\Magento\Framework\Encryption\Encryptor::class, ['getHash']);
        $this->_encryptor->expects($this->any())
            ->method('getHash')
            ->willReturnArgument(0);
        $routeParamsResolver = $this->createMock(\Magento\Framework\Url\RouteParamsResolver::class);
        $this->routeParamsResolverFactoryMock = $this->createMock(\Magento\Framework\Url\RouteParamsResolverFactory::class);
        $this->routeParamsResolverFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($routeParamsResolver);
        /** @var HostChecker|\PHPUnit_Framework_MockObject_MockObject $hostCheckerMock */
        $hostCheckerMock = $this->createMock(HostChecker::class);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );
        $this->_model = $objectManager->getObject(
            \Magento\Backend\Model\Url::class,
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
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->_model->setRequest($this->_requestMock);
    }

    public function testFindFirstAvailableMenuDenied()
    {
        $user = $this->createMock(\Magento\User\Model\User::class);
        $user->expects($this->once())->method('setHasAvailableResources')->with($this->equalTo(false));
        $mockSession = $this->createPartialMock(\Magento\Backend\Model\Auth\Session::class, ['getUser', 'isAllowed']);

        $mockSession->expects($this->any())->method('getUser')->will($this->returnValue($user));

        $this->_model->setSession($mockSession);

        $this->_menuMock->expects($this->any())->method('getFirstAvailableChild')->will($this->returnValue(null));

        $this->assertEquals('*/*/denied', $this->_model->findFirstAvailableMenu());
    }

    public function testFindFirstAvailableMenu()
    {
        $user = $this->createMock(\Magento\User\Model\User::class);
        $mockSession = $this->createPartialMock(\Magento\Backend\Model\Auth\Session::class, ['getUser', 'isAllowed']);

        $mockSession->expects($this->any())->method('getUser')->will($this->returnValue($user));

        $this->_model->setSession($mockSession);

        $itemMock = $this->createMock(\Magento\Backend\Model\Menu\Item::class);
        $itemMock->expects($this->once())->method('getAction')->will($this->returnValue('adminhtml/user'));
        $this->_menuMock->expects($this->any())->method('getFirstAvailable')->will($this->returnValue($itemMock));

        $this->assertEquals('adminhtml/user', $this->_model->findFirstAvailableMenu());
    }

    public function testGetStartupPageUrl()
    {
        $this->assertEquals('adminhtml/user_role', (string)$this->_model->getStartupPageUrl());
    }

    public function testGetAreaFrontName()
    {
        $helperMock = $this->createMock(\Magento\Backend\Helper\Data::class);
        $helperMock->expects(
            $this->once()
        )->method(
            'getAreaFrontName'
        )->will(
            $this->returnValue($this->_areaFrontName)
        );

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $urlModel = $helper->getObject(
            \Magento\Backend\Model\Url::class,
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
        )->will(
            $this->returnValue(null)
        );
        $this->_requestMock->expects($this->once())->method('getRouteName')->will($this->returnValue($routeName));
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getControllerName'
        )->will(
            $this->returnValue($controllerName)
        );
        $this->_requestMock->expects($this->once())->method('getActionName')->will($this->returnValue($actionName));
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
        )->will(
            $this->returnValue('adminhtml')
        );

        $this->_requestMock->expects(
            $this->at(1)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'route_name'
        )->will(
            $this->returnValue('adminhtml')
        );

        $this->_requestMock->expects(
            $this->at(2)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'controller_name'
        )->will(
            $this->returnValue('catalog')
        );

        $this->_requestMock->expects(
            $this->at(3)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'controller_name'
        )->will(
            $this->returnValue('catalog')
        );

        $this->_requestMock->expects(
            $this->at(4)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'action_name'
        )->will(
            $this->returnValue('index')
        );

        $this->_requestMock->expects(
            $this->at(5)
        )->method(
            'getBeforeForwardInfo'
        )->with(
            'action_name'
        )->will(
            $this->returnValue('index')
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
