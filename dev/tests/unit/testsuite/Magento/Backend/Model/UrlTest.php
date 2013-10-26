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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Backend\Model\Url
 */
namespace Magento\Backend\Model;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    protected  $_model;

    protected $_areaFrontName = 'backendArea';

    /**
     * Mock menu model
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeConfigMock;

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
    protected $_coreDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authSessionMock;

    protected function setUp()
    {
        $this->_menuMock = $this->getMock('Magento\Backend\Model\Menu', array(), array(), '', false);

        $this->_menuConfigMock = $this->getMock('Magento\Backend\Model\Menu\Config', array(), array(), '', false);
        $this->_menuConfigMock->expects($this->any())->method('getMenu')->will($this->returnValue($this->_menuMock));

        $this->_coreSessionMock = $this->getMock(
            'Magento\Core\Model\Session', array('getFormKey'), array(), '', false
        );
        $this->_coreSessionMock->expects($this->any())->method('getFormKey')->will($this->returnValue('salt'));

        $mockItem = $this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false);
        $mockItem->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $mockItem->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $mockItem->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('Magento_Adminhtml::system_acl_roles'));
        $mockItem->expects($this->any())->method('getAction')->will($this->returnValue('adminhtml/user_role'));

        $this->_menuMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('Magento_Adminhtml::system_acl_roles'))
            ->will($this->returnValue($mockItem));

        $helperMock = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $helperMock->expects($this->any())->method('getAreaFrontName')
            ->will($this->returnValue($this->_areaFrontName));
        $this->_storeConfigMock = $this->getMock('Magento\Core\Model\Store\Config', array(), array(), '', false);
        $this->_storeConfigMock->expects($this->any())
            ->method('getConfig')
            ->with(\Magento\Backend\Model\Url::XML_PATH_STARTUP_MENU_ITEM)
            ->will($this->returnValue('Magento_Adminhtml::system_acl_roles'));

        $this->_coreDataMock = $this->getMock('Magento\Core\Helper\Data', array('getHash'), array(), '', false);
        $this->_coreDataMock->expects($this->any())->method('getHash')->will($this->returnArgument(0));

        $this->_authSessionMock = $this->getMock('Magento\Backend\Model\Auth\Session', array(), array(),
            '', false, false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject('Magento\Backend\Model\Url', array(
            'coreStoreConfig' => $this->_storeConfigMock,
            'backendHelper'   => $helperMock,
            'session'         => $this->_coreSessionMock,
            'menuConfig'      => $this->_menuConfigMock,
            'coreData'        => $this->_coreDataMock,
            'authSession'     => $this->_authSessionMock
        ));

        $this->_requestMock = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $this->_model->setRequest($this->_requestMock);
    }

    public function testFindFirstAvailableMenuDenied()
    {
        $user = $this->getMock('Magento\User\Model\User', array(), array(), '', false);
        $user->expects($this->once())
            ->method('setHasAvailableResources')
            ->with($this->equalTo(false));
        $mockSession = $this->getMock('Magento\Backend\Model\Auth\Session',
            array('getUser', 'isAllowed'),
            array(),
            '',
            false
        );

        $mockSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->_model->setSession($mockSession);

        $this->_menuMock->expects($this->any())
            ->method('getFirstAvailableChild')
            ->will($this->returnValue(null));

        $this->assertEquals('*/*/denied', $this->_model->findFirstAvailableMenu());
    }

    public function testFindFirstAvailableMenu()
    {
        $user = $this->getMock('Magento\User\Model\User', array(), array(), '', false);
        $mockSession = $this->getMock('Magento\Backend\Model\Auth\Session',
            array('getUser', 'isAllowed'),
            array(),
            '',
            false
        );

        $mockSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->_model->setSession($mockSession);

        $itemMock = $this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false);
        $itemMock->expects($this->once())->method('getAction')->will($this->returnValue('adminhtml/user'));
        $this->_menuMock->expects($this->any())
            ->method('getFirstAvailable')
            ->will($this->returnValue($itemMock));

        $this->assertEquals('adminhtml/user', $this->_model->findFirstAvailableMenu());
    }

    public function testGetStartupPageUrl()
    {
        $this->assertEquals('adminhtml/user_role', (string)$this->_model->getStartupPageUrl());
    }

    public function testGetAreaFrontName()
    {
        $helperMock = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $helperMock->expects($this->once())->method('getAreaFrontName')
            ->will($this->returnValue($this->_areaFrontName));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $urlModel = $helper->getObject('Magento\Backend\Model\Url', array(
            'backendHelper'   => $helperMock,
            'authSession'     => $this->_authSessionMock
        ));
        $urlModel->getAreaFrontName();
    }

    public function testGetActionPath()
    {
        $moduleFrontName = 'moduleFrontName';
        $controllerName = 'controllerName';
        $actionName = 'actionName';

        $this->_model->setRouteName($moduleFrontName);
        $this->_model->setRouteFrontName($moduleFrontName);
        $this->_model->setControllerName($controllerName);
        $this->_model->setActionName($actionName);

        $actionPath = $this->_model->getActionPath();

        $this->assertNotEmpty($actionPath);
        $this->assertStringStartsWith($this->_areaFrontName . '/', $actionPath);
        $this->assertStringMatchesFormat($this->_areaFrontName . '/%s/%s/%s', $actionPath);
    }

    public function testGetActionPathWhenAreaFrontNameIsEmpty()
    {
        $helperMock = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $helperMock->expects($this->once())->method('getAreaFrontName')
            ->will($this->returnValue(''));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $urlModel = $helper->getObject('Magento\Backend\Model\Url', array(
            'backendHelper'   => $helperMock,
            'authSession'     => $this->_authSessionMock
        ));

        $moduleFrontName = 'moduleFrontName';
        $controllerName = 'controllerName';
        $actionName = 'actionName';

        $urlModel->setRouteName($moduleFrontName);
        $urlModel->setRouteFrontName($moduleFrontName);
        $urlModel->setControllerName($controllerName);
        $urlModel->setActionName($actionName);

        $actionPath = $urlModel->getActionPath();

        $this->assertNotEmpty($actionPath);
        $this->assertStringStartsWith($moduleFrontName . '/', $actionPath);
        $this->assertStringMatchesFormat($moduleFrontName . '/%s/%s', $actionPath);
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

        $this->_requestMock->expects($this->exactly(3))->method('getBeforeForwardInfo')->will($this->returnValue(null));
        $this->_requestMock->expects($this->once())->method('getRouteName')->will($this->returnValue($routeName));
        $this->_requestMock
            ->expects($this->once())->method('getControllerName')->will($this->returnValue($controllerName));
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

        $this->_requestMock->expects($this->at(0))
            ->method('getBeforeForwardInfo')
            ->with('route_name')
            ->will($this->returnValue('adminhtml'));

        $this->_requestMock->expects($this->at(1))
            ->method('getBeforeForwardInfo')
            ->with('route_name')
            ->will($this->returnValue('adminhtml'));

        $this->_requestMock->expects($this->at(2))
            ->method('getBeforeForwardInfo')
            ->with('controller_name')
            ->will($this->returnValue('catalog'));

        $this->_requestMock->expects($this->at(3))
            ->method('getBeforeForwardInfo')
            ->with('controller_name')
            ->will($this->returnValue('catalog'));

        $this->_requestMock->expects($this->at(4))
            ->method('getBeforeForwardInfo')
            ->with('action_name')
            ->will($this->returnValue('index'));

        $this->_requestMock->expects($this->at(5))
            ->method('getBeforeForwardInfo')
            ->with('action_name')
            ->will($this->returnValue('index'));

        $this->_model->setRequest($this->_requestMock);
        $keyFromRequest = $this->_model->getSecretKey();
        $this->assertEquals($keyFromParams, $keyFromRequest);
    }
}
