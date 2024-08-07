<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Controller\Adminhtml\Session;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Controller\Adminhtml\Session\LogoutAll;
use Magento\Security\Model\AdminSessionsManager;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Test\Unit\Controller\Adminhtml\Session\LogoutAll testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LogoutAllTest extends TestCase
{
    /**
     * @var  LogoutAll
     */
    protected $controller;

    /**
     * @var Context
     */
    protected $contextMock;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var ActionFlag
     */
    protected $actionFlagMock;

    /**
     * @var ResponseInterface
     */
    protected $responseMock;

    /**
     * @var Data
     */
    protected $backendHelperMock;

    /**
     * @var  ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addSuccessMessage', 'addErrorMessage', 'addExceptionMessage'])
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIsUrlNotice'])
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);

        $this->sessionsManager = $this->createPartialMock(
            AdminSessionsManager::class,
            ['logoutOtherUserSessions']
        );

        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);

        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);

        $this->backendHelperMock = $this->createPartialMock(Data::class, ['getUrl']);
        $this->contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->backendHelperMock);

        $this->controller = $this->objectManager->getObject(
            LogoutAll::class,
            [
                'context' => $this->contextMock,
                'sessionsManager' => $this->sessionsManager
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $successMessage = 'All other open sessions for this account were terminated.';
        $this->sessionsManager->expects($this->once())
            ->method('logoutOtherUserSessions');
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with($successMessage);
        $this->messageManager->expects($this->never())
            ->method('addErrorMessage');
        $this->messageManager->expects($this->never())
            ->method('addExceptionMessage');
        $this->responseMock->expects($this->once())
            ->method('setRedirect');
        $this->actionFlagMock->expects($this->once())
            ->method('get')
            ->with('', AbstractAction::FLAG_IS_URLS_CHECKED);
        $this->backendHelperMock->expects($this->once())
            ->method('getUrl');
        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testExecuteLocalizedException()
    {
        $phrase = new Phrase('some error');
        $this->sessionsManager->expects($this->once())
            ->method('logoutOtherUserSessions')
            ->willThrowException(new LocalizedException($phrase));
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with($phrase);
        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testExecuteException()
    {
        $phrase = new Phrase('We couldn\'t logout because of an error.');
        $this->sessionsManager->expects($this->once())
            ->method('logoutOtherUserSessions')
            ->willThrowException(new \Exception());
        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with(new \Exception(), $phrase);
        $this->controller->execute();
    }
}
