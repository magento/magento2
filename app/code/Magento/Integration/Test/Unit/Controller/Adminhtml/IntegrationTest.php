<?php
/**
 * \Magento\Integration\Controller\Adminhtml
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Controller\Adminhtml;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Security\Model\SecurityCookie;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Integration\Controller\Adminhtml\Integration */
    protected $_controller;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  $objectManagerHelper */
    protected $_objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var \Magento\Backend\Model\View\Layout\Filter\Acl|\PHPUnit_Framework_MockObject_MockObject */
    protected $_layoutFilterMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_configMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_eventManagerMock;

    /** @var \Magento\Framework\Translate|\PHPUnit_Framework_MockObject_MockObject */
    protected $_translateModelMock;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $_backendSessionMock;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $_backendActionCtxMock;

    /** @var SecurityCookie|\PHPUnit_Framework_MockObject_MockObject */
    protected $securityCookieMock;

    /** @var \Magento\Integration\Api\IntegrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_integrationSvcMock;

    /** @var \Magento\Integration\Api\OauthServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_oauthSvcMock;

    /** @var \Magento\Backend\Model\Auth|\PHPUnit_Framework_MockObject_MockObject */
    protected $_authMock;

    /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject */
    protected $_userMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $_registryMock;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $_requestMock;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $_responseMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_messageManager;

    /** @var \Magento\Framework\Config\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_configScopeMock;

    /** @var \Magento\Integration\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_integrationHelperMock;

    /** @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_viewMock;

    /** @var \Magento\Framework\View\Model\Layout\Merge|\PHPUnit_Framework_MockObject_MockObject */
    protected $_layoutMergeMock;

    /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_layoutMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_escaper;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /** Sample integration ID */
    const INTEGRATION_ID = 1;

    /**
     * Setup object manager and initialize mocks
     */
    protected function setUp()
    {
        /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  $objectManagerHelper */
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        // Initialize mocks which are used in several test cases
        $this->_configMock = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $this->_layoutFilterMock = $this->getMockBuilder(
            \Magento\Backend\Model\Layout\Filter\Acl::class
        )->disableOriginalConstructor()->getMock();
        $this->_backendSessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->setMethods(['__call', 'getIntegrationData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'load', 'performIdentityCheck'])
            ->getMock();

        $this->_translateModelMock = $this->getMockBuilder(
            \Magento\Framework\TranslateInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_integrationSvcMock = $this->getMockBuilder(
            \Magento\Integration\Api\IntegrationServiceInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_oauthSvcMock = $this->getMockBuilder(
            \Magento\Integration\Api\OauthServiceInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_authMock = $this->getMockBuilder(\Magento\Backend\Model\Auth::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'logout'])
            ->getMock();
        $this->_requestMock = $this->getMockBuilder(
            \Magento\Framework\App\Request\Http::class
        )->disableOriginalConstructor()->getMock();
        $this->_responseMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()->getMock();
        $this->_registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_configScopeMock = $this->getMockBuilder(
            \Magento\Framework\Config\ScopeInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_integrationHelperMock = $this->getMockBuilder(
            \Magento\Integration\Helper\Data::class
        )->disableOriginalConstructor()->getMock();
        $this->_messageManager = $this->getMockBuilder(
            \Magento\Framework\Message\ManagerInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_escaper = $this->getMockBuilder(
            \Magento\Framework\Escaper::class
        )->setMethods(
            ['escapeHtml']
        )->disableOriginalConstructor()->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityCookieMock = $this->getMockBuilder(SecurityCookie::class)
            ->disableOriginalConstructor()
            ->setMethods(['setLogoutReasonCookie'])
            ->getMock();
    }

    /**
     * @param string $actionName
     * @return \Magento\Integration\Controller\Adminhtml\Integration
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _createIntegrationController($actionName)
    {
        // Mock Layout passed into constructor
        $this->_viewMock = $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)
            ->getMock();
        $this->_layoutMock = $this->getMock(\Magento\Framework\View\LayoutInterface::class);
        $this->_layoutMergeMock = $this->getMockBuilder(
            \Magento\Framework\View\Model\Layout\Merge::class
        )->disableOriginalConstructor()->getMock();
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getUpdate'
        )->will(
            $this->returnValue($this->_layoutMergeMock)
        );
        $testElement = new \Magento\Framework\Simplexml\Element('<test>test</test>');
        $this->_layoutMock->expects($this->any())->method('getNode')->will($this->returnValue($testElement));
        // for _setActiveMenu
        $this->_viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($this->_layoutMock));
        $blockMock = $this->getMockBuilder(\Magento\Backend\Block\Menu::class)->disableOriginalConstructor()->getMock();
        $menuMock = $this->getMock(
            \Magento\Backend\Model\Menu::class,
            [],
            [$this->getMock('Psr\Log\LoggerInterface')]
        );
        $loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $loggerMock->expects($this->any())->method('critical')->will($this->returnSelf());
        $menuMock->expects($this->any())->method('getParentItems')->will($this->returnValue([]));
        $blockMock->expects($this->any())->method('getMenuModel')->will($this->returnValue($menuMock));
        $this->_layoutMock->expects($this->any())->method('getMessagesBlock')->will($this->returnValue($blockMock));
        $this->_layoutMock->expects($this->any())->method('getBlock')->will($this->returnValue($blockMock));
        $this->_viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->viewConfigMock);
        $this->viewConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->_authMock->expects(
            $this->any()
        )->method(
            'getUser'
        )->will(
            $this->returnValue($this->_userMock)
        );

        $this->_userMock->expects($this->any())
            ->method('load')
            ->willReturn($this->_userMock);

        $this->_backendSessionMock->expects($this->any())
            ->method('getIntegrationData')
            ->willReturn(['all_resources' => 1]);

        $contextParameters = [
            'view' => $this->_viewMock,
            'objectManager' => $this->_objectManagerMock,
            'session' => $this->_backendSessionMock,
            'translator' => $this->_translateModelMock,
            'request' => $this->_requestMock,
            'response' => $this->_responseMock,
            'messageManager' => $this->_messageManager,
            'resultRedirectFactory' => $this->resultRedirectFactory,
            'resultFactory' => $this->resultFactory,
            'auth' => $this->_authMock,
            'eventManager' => $this->_eventManagerMock
        ];

        $this->_backendActionCtxMock = $this->_objectManagerHelper->getObject(
            \Magento\Backend\App\Action\Context::class,
            $contextParameters
        );

        $integrationCollection =
            $this->getMockBuilder(\Magento\Integration\Model\ResourceModel\Integration\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addUnsecureUrlsFilter', 'getSize'])
            ->getMock();
        $integrationCollection->expects($this->any())
            ->method('addUnsecureUrlsFilter')
            ->will($this->returnValue($integrationCollection));
        $integrationCollection->expects($this->any())
            ->method('getSize')
            ->will($this->returnValue(0));

        $subControllerParams = [
            'context' => $this->_backendActionCtxMock,
            'integrationService' => $this->_integrationSvcMock,
            'oauthService' => $this->_oauthSvcMock,
            'registry' => $this->_registryMock,
            'logger' => $loggerMock,
            'integrationData' => $this->_integrationHelperMock,
            'escaper' => $this->_escaper,
            'integrationCollection' => $integrationCollection,
        ];
        /** Create IntegrationController to test */
        $controller = $this->_objectManagerHelper->getObject(
            '\\Magento\\Integration\\Controller\\Adminhtml\\Integration\\' . $actionName,
            $subControllerParams
        );
        if ($actionName == 'Save') {
            $reflection = new \ReflectionClass(get_class($controller));
            $reflectionProperty = $reflection->getProperty('securityCookie');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($controller, $this->securityCookieMock);
        }
        return $controller;
    }

    /**
     * Common mock 'expect' pattern.
     * Calls that need to be mocked out when
     * \Magento\Backend\App\AbstractAction loadLayout() and renderLayout() are called.
     */
    protected function _verifyLoadAndRenderLayout()
    {
        $map = [
            [\Magento\Framework\App\Config\ScopeConfigInterface::class, $this->_configMock],
            [\Magento\Backend\Model\Layout\Filter\Acl::class, $this->_layoutFilterMock],
            [\Magento\Backend\Model\Session::class, $this->_backendSessionMock],
            [\Magento\Framework\TranslateInterface::class, $this->_translateModelMock],
            [\Magento\Framework\Config\ScopeInterface::class, $this->_configScopeMock],
        ];
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap($map));
    }

    /**
     * Return sample Integration Data
     *
     * @return \Magento\Framework\DataObject
     */
    protected function _getSampleIntegrationData()
    {
        return new \Magento\Framework\DataObject(
            [
                Info::DATA_NAME => 'nameTest',
                Info::DATA_ID => self::INTEGRATION_ID,
                'id' => self::INTEGRATION_ID,
                Info::DATA_EMAIL => 'test@magento.com',
                Info::DATA_ENDPOINT => 'http://magento.ll/endpoint',
                Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_MANUAL,
            ]
        );
    }

    /**
     * Return integration model mock with sample data.
     *
     * @return \Magento\Integration\Model\Integration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getIntegrationModelMock()
    {
        $integrationModelMock = $this->getMock(
            \Magento\Integration\Model\Integration::class,
            ['save', '__wakeup'],
            [],
            '',
            false
        );

        $integrationModelMock->expects($this->any())->method('setStatus')->will($this->returnSelf());
        $integrationModelMock->expects(
            $this->any()
        )->method(
            'getData'
        )->will(
            $this->returnValue($this->_getSampleIntegrationData())
        );

        return $integrationModelMock;
    }
}
