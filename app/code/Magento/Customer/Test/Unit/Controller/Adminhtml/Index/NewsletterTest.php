<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Customer\Controller\Adminhtml\Index\Newsletter;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Newsletter\Model\Subscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Customer\Controller\Adminhtml\Index controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewsletterTest extends TestCase
{
    /**
     * Request mock instance
     *
     * @var MockObject|RequestInterface
     */
    protected $_request;

    /**
     * Response mock instance
     *
     * @var MockObject|ResponseInterface
     */
    protected $_response;

    /**
     * Instance of mocked tested object
     *
     * @var MockObject|Index
     */
    protected $_testedObject;

    /**
     * ObjectManager mock instance
     *
     * @var MockObject|\Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var MockObject|AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * Session mock instance
     *
     * @var MockObject|\Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * Backend helper mock instance
     *
     * @var MockObject|Data
     */
    protected $_helper;

    /**
     * @var MockObject|ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Layout|MockObject
     */
    protected $resultLayoutMock;

    /**
     * @var MockObject
     */
    protected $pageConfigMock;

    /**
     * @var MockObject
     */
    protected $titleMock;

    /**
     * @var MockObject
     */
    protected $layoutInterfaceMock;

    /**
     * @var MockObject
     */
    protected $viewInterfaceMock;

    /**
     * @var LayoutFactory|MockObject
     */
    protected $resultLayoutFactoryMock;

    /**
     * Prepare required values
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->_request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_response = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setRedirect', 'getHeader', '__wakeup'])
            ->getMock();

        $this->_response->expects(
            $this->any()
        )->method(
            'getHeader'
        )->with(
            'X-Frame-Options'
        )->willReturn(
            true
        );

        $this->_objectManager = $this->getMockBuilder(
            ObjectManager::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['get', 'create']
            )->getMock();
        $frontControllerMock = $this->getMockBuilder(
            FrontController::class
        )->disableOriginalConstructor()
            ->getMock();

        $actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_session = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->addMethods(
                ['setIsUrlNotice', '__wakeup']
            )->getMock();
        $this->_session->expects($this->any())->method('setIsUrlNotice');

        $this->_helper = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['getUrl']
            )->getMock();

        $this->messageManager = $this->getMockBuilder(
            Manager::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['addSuccess', 'addMessage', 'addException']
            )->getMock();

        $addContextArgs = [
            'getTranslator',
            'getFrontController',
            'getLayoutFactory',
            'getTitle'
        ];

        $contextArgs = [
            'getHelper',
            'getSession',
            'getAuthorization',
            'getObjectManager',
            'getActionFlag',
            'getMessageManager',
            'getEventManager',
            'getRequest',
            'getResponse',
            'getView'
        ];
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->addMethods($addContextArgs)
            ->onlyMethods($contextArgs)
            ->getMock();
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->_request);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->_response);
        $contextMock->expects(
            $this->any()
        )->method(
            'getObjectManager'
        )->willReturn(
            $this->_objectManager
        );
        $contextMock->expects(
            $this->any()
        )->method(
            'getFrontController'
        )->willReturn(
            $frontControllerMock
        );
        $contextMock->expects($this->any())->method('getActionFlag')->willReturn($actionFlagMock);

        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->_helper);
        $contextMock->expects($this->any())->method('getSession')->willReturn($this->_session);
        $contextMock->expects(
            $this->any()
        )->method(
            'getMessageManager'
        )->willReturn(
            $this->messageManager
        );
        $this->titleMock =  $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->viewInterfaceMock =  $this->getMockBuilder(ViewInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->viewInterfaceMock->expects($this->any())->method('loadLayout')->willReturnSelf();
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewInterfaceMock);
        $this->resultLayoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAccountManagement = $this->getMockBuilder(
            AccountManagementInterface::class
        )->getMock();
        $this->resultLayoutFactoryMock = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $args = [
            'context' => $contextMock,
            'customerAccountManagement' => $this->customerAccountManagement,
            'resultLayoutFactory' => $this->resultLayoutFactoryMock
        ];

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_testedObject = $helperObjectManager->getObject(
            Newsletter::class,
            $args
        );
    }

    public function testNewsletterAction()
    {
        $subscriberMock = $this->createMock(Subscriber::class);
        $this->resultLayoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultLayoutMock);
        $subscriberMock->expects($this->once())
            ->method('loadByCustomerId');
        $this->_objectManager
            ->expects($this->any())
            ->method('create')
            ->with(Subscriber::class)
            ->willReturn($subscriberMock);

        $this->assertInstanceOf(
            Layout::class,
            $this->_testedObject->execute()
        );
    }
}
