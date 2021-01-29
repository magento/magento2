<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

/**
 * Unit test for \Magento\Customer\Controller\Adminhtml\Index controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewsletterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Request mock instance
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Response mock instance
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * Instance of mocked tested object
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Customer\Controller\Adminhtml\Index
     */
    protected $_testedObject;

    /**
     * ObjectManager mock instance
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * Session mock instance
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * Backend helper mock instance
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\View\Result\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultLayoutMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $titleMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutInterfaceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewInterfaceMock;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory|\PHPUnit\Framework\MockObject\MockObject
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
        $this->_request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_response = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect', 'getHeader', '__wakeup'])
            ->getMock();

        $this->_response->expects(
            $this->any()
        )->method(
            'getHeader'
        )->with(
            $this->equalTo('X-Frame-Options')
        )->willReturn(
            true
        );

        $this->_objectManager = $this->getMockBuilder(
            \Magento\Framework\App\ObjectManager::class
        )->disableOriginalConstructor()->setMethods(
            ['get', 'create']
        )->getMock();
        $frontControllerMock = $this->getMockBuilder(
            \Magento\Framework\App\FrontController::class
        )->disableOriginalConstructor()->getMock();

        $actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_session = $this->getMockBuilder(
            \Magento\Backend\Model\Session::class
        )->disableOriginalConstructor()->setMethods(
            ['setIsUrlNotice', '__wakeup']
        )->getMock();
        $this->_session->expects($this->any())->method('setIsUrlNotice');

        $this->_helper = $this->getMockBuilder(
            \Magento\Backend\Helper\Data::class
        )->disableOriginalConstructor()->setMethods(
            ['getUrl']
        )->getMock();

        $this->messageManager = $this->getMockBuilder(
            \Magento\Framework\Message\Manager::class
        )->disableOriginalConstructor()->setMethods(
            ['addSuccess', 'addMessage', 'addException']
        )->getMock();

        $contextArgs = [
            'getHelper',
            'getSession',
            'getAuthorization',
            'getTranslator',
            'getObjectManager',
            'getFrontController',
            'getActionFlag',
            'getMessageManager',
            'getLayoutFactory',
            'getEventManager',
            'getRequest',
            'getResponse',
            'getTitle',
            'getView'
        ];
        $contextMock = $this->getMockBuilder(
            \Magento\Backend\App\Action\Context::class
        )->disableOriginalConstructor()->setMethods(
            $contextArgs
        )->getMock();
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
        $this->titleMock =  $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->viewInterfaceMock =  $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewInterfaceMock->expects($this->any())->method('loadLayout')->willReturnSelf();
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewInterfaceMock);
        $this->resultLayoutMock = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAccountManagement = $this->getMockBuilder(
            \Magento\Customer\Api\AccountManagementInterface::class
        )->getMock();
        $this->resultLayoutFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\LayoutFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $args = [
            'context' => $contextMock,
            'customerAccountManagement' => $this->customerAccountManagement,
            'resultLayoutFactory' => $this->resultLayoutFactoryMock
        ];

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_testedObject = $helperObjectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Index\Newsletter::class,
            $args
        );
    }

    public function testNewsletterAction()
    {
        $subscriberMock = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $this->resultLayoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultLayoutMock);
        $subscriberMock->expects($this->once())
            ->method('loadByCustomerId');
        $this->_objectManager
            ->expects($this->any())
            ->method('create')
            ->with(\Magento\Newsletter\Model\Subscriber::class)
            ->willReturn($subscriberMock);

        $this->assertInstanceOf(
            \Magento\Framework\View\Result\Layout::class,
            $this->_testedObject->execute()
        );
    }
}
