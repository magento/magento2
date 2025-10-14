<?php declare(strict_types=1);
/**
 * \Magento\Integration\Controller\Adminhtml
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Menu;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Simplexml\Element;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Helper\Data;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Model\ResourceModel\Integration\Collection;
use Magento\Security\Model\SecurityCookie;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class IntegrationTestCase extends TestCase
{
    /** @var \Magento\Integration\Controller\Adminhtml\Integration */
    protected $_controller;

    /** @var ObjectManager  $objectManagerHelper */
    protected $_objectManagerHelper;

    /** @var ObjectManagerInterface|MockObject */
    protected $_objectManagerMock;

    /** @var ScopeConfigInterface|MockObject */
    protected $_configMock;

    /** @var ManagerInterface|MockObject */
    protected $_eventManagerMock;

    /** @var Translate|MockObject */
    protected $_translateModelMock;

    /** @var Session|MockObject */
    protected $_backendSessionMock;

    /** @var Context|MockObject */
    protected $_backendActionCtxMock;

    /** @var SecurityCookie|MockObject */
    protected $securityCookieMock;

    /** @var IntegrationServiceInterface|MockObject */
    protected $_integrationSvcMock;

    /** @var OauthServiceInterface|MockObject */
    protected $_oauthSvcMock;

    /** @var Auth|MockObject */
    protected $_authMock;

    /** @var User|MockObject */
    protected $_userMock;

    /** @var Registry|MockObject */
    protected $_registryMock;

    /** @var Http|MockObject */
    protected $_requestMock;

    /** @var \Magento\Framework\App\Response\Http|MockObject */
    protected $_responseMock;

    /** @var  MockObject */
    protected $_messageManager;

    /** @var ScopeInterface|MockObject */
    protected $_configScopeMock;

    /** @var \Magento\Integration\Helper\Data|MockObject */
    protected $_integrationHelperMock;

    /** @var ViewInterface|MockObject */
    protected $_viewMock;

    /** @var Merge|MockObject */
    protected $_layoutMergeMock;

    /** @var LayoutInterface|MockObject */
    protected $_layoutMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var Config|MockObject
     */
    protected $viewConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    /**
     * @var Escaper|MockObject
     */
    protected $_escaper;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    /** Sample integration ID */
    const INTEGRATION_ID = 1;

    /**
     * Setup object manager and initialize mocks
     */
    protected function setUp(): void
    {
        /** @var ObjectManager  $objectManagerHelper */
        $this->_objectManagerHelper = new ObjectManager($this);
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        // Initialize mocks which are used in several test cases
        $this->_configMock = $this->getMockBuilder(
            ScopeConfigInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['dispatch'])
            ->getMockForAbstractClass();
        $this->_backendSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getIntegrationData'])
            ->onlyMethods(['__call'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'load', 'performIdentityCheck'])
            ->getMock();

        $this->_translateModelMock = $this->getMockBuilder(
            TranslateInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_integrationSvcMock = $this->getMockBuilder(
            IntegrationServiceInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_oauthSvcMock = $this->getMockBuilder(
            OauthServiceInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_authMock = $this->getMockBuilder(Auth::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUser', 'logout'])
            ->getMock();
        $this->_requestMock = $this->getMockBuilder(
            Http::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_responseMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_configScopeMock = $this->getMockBuilder(
            ScopeInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_integrationHelperMock = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_messageManager = $this->getMockBuilder(
            \Magento\Framework\Message\ManagerInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_escaper = $this->getMockBuilder(
            Escaper::class
        )->onlyMethods(
            ['escapeHtml']
        )->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityCookieMock = $this->getMockBuilder(SecurityCookie::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setLogoutReasonCookie'])
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
        $this->_viewMock = $this->getMockBuilder(ViewInterface::class)
            ->getMock();
        $this->_layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->addMethods(['getNode'])
            ->getMockForAbstractClass();
        $this->_layoutMergeMock = $this->getMockBuilder(
            Merge::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getUpdate'
        )->willReturn(
            $this->_layoutMergeMock
        );
        $testElement = new Element('<test>test</test>');
        $this->_layoutMock->expects($this->any())->method('getNode')->willReturn($testElement);
        // for _setActiveMenu
        $this->_viewMock->expects($this->any())->method('getLayout')->willReturn($this->_layoutMock);
        $blockMock = $this->getMockBuilder(Menu::class)
            ->disableOriginalConstructor()
            ->getMock();
        $menuMock = $this->getMockBuilder(\Magento\Backend\Model\Menu::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(LoggerInterface::class)])
            ->getMock();
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $loggerMock->expects($this->any())->method('critical')->willReturnSelf();
        $menuMock->expects($this->any())->method('getParentItems')->willReturn([]);
        $blockMock->expects($this->any())->method('getMenuModel')->willReturn($menuMock);
        $this->_layoutMock->expects($this->any())->method('getMessagesBlock')->willReturn($blockMock);
        $this->_layoutMock->expects($this->any())->method('getBlock')->willReturn($blockMock);
        $this->_viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->viewConfigMock);
        $this->viewConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->_authMock->expects(
            $this->any()
        )->method(
            'getUser'
        )->willReturn(
            $this->_userMock
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
            Context::class,
            $contextParameters
        );

        $integrationCollection =
            $this->getMockBuilder(Collection::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['addUnsecureUrlsFilter', 'getSize'])
                ->getMock();
        $integrationCollection->expects($this->any())
            ->method('addUnsecureUrlsFilter')
            ->willReturn($integrationCollection);
        $integrationCollection->expects($this->any())
            ->method('getSize')
            ->willReturn(0);

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
            [ScopeConfigInterface::class, $this->_configMock],
            [Session::class, $this->_backendSessionMock],
            [TranslateInterface::class, $this->_translateModelMock],
            [ScopeInterface::class, $this->_configScopeMock],
        ];
        $this->_objectManagerMock->expects($this->any())->method('get')->willReturnMap($map);
    }

    /**
     * Return sample Integration Data
     *
     * @return DataObject
     */
    protected function _getSampleIntegrationData()
    {
        return new DataObject(
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
     * @return \Magento\Integration\Model\Integration|MockObject
     */
    protected function _getIntegrationModelMock()
    {
        $integrationModelMock = $this->getMockBuilder(\Magento\Integration\Model\Integration::class)->addMethods(
            ['setStatus']
        )
            ->onlyMethods(['save', '__wakeup', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $integrationModelMock->expects($this->any())->method('setStatus')->willReturnSelf();
        $integrationModelMock->expects(
            $this->any()
        )->method(
            'getData'
        )->willReturn(
            $this->_getSampleIntegrationData()
        );

        return $integrationModelMock;
    }
}
