<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\System\Account;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\System\Account\Edit\Form;
use Magento\Backend\Controller\Adminhtml\System\Account;
use Magento\Backend\Controller\Adminhtml\System\Account\Save;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Locale\Manager;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\TranslateInterface;
use Magento\Framework\Validator\Locale;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Backend\Controller\Adminhtml\System\Account controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var Account
     */
    protected $controller;

    /**
     * @var MockObject|RequestInterface
     */
    protected $requestMock;

    /**
     * @var MockObject|ResponseInterface
     */
    protected $responseMock;

    /**
     * @var MockObject|ObjectManager
     */
    protected $objectManagerMock;

    /**
     * @var MockObject|\Magento\Framework\Message\ManagerInterface
     */
    protected $messagesMock;

    /**
     * @var MockObject|Data
     */
    protected $helperMock;

    /**
     * @var MockObject|Session
     */
    protected $authSessionMock;

    /**
     * @var MockObject|User
     */
    protected $userMock;

    /**
     * @var MockObject|Locale
     */
    protected $validatorMock;

    /**
     * @var MockObject|Manager
     */
    protected $managerMock;

    /**
     * @var MockObject|TranslateInterface
     */
    protected $translatorMock;

    /**
     * @var MockObject|EventManagerInterface
     */
    protected $eventManagerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOriginalPathInfo'])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'create'])
            ->getMock();
        $frontControllerMock = $this->getMockBuilder(FrontController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl'])
            ->getMock();
        $this->messagesMock = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addSuccessMessage'])
            ->getMockForAbstractClass();

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getUser'])
            ->getMock();

        $this->userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'load',
                    'save',
                    'sendNotificationEmailsIfRequired',
                    'performIdentityCheck',
                    'validate',
                    '__sleep',
                    '__wakeup'
                ]
            )
            ->getMock();

        $this->validatorMock = $this->getMockBuilder(Locale::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isValid'])
            ->getMock();

        $this->managerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['switchBackendInterfaceLocale'])
            ->getMock();

        $this->translatorMock = $this->getMockBuilder(TranslateInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $contextMock = $this->getMockBuilder(Context::class)
            ->addMethods(['getFrontController', 'getTranslator'])
            ->onlyMethods([
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getHelper',
                'getMessageManager',
                'getResultFactory'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())->method('getFrontController')->willReturn($frontControllerMock);
        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->helperMock);
        $contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messagesMock);
        $contextMock->expects($this->any())->method('getTranslator')->willReturn($this->translatorMock);
        $contextMock->expects($this->once())->method('getResultFactory')->willReturn($resultFactory);

        $args = ['context' => $contextMock];

        $testHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $testHelper->getObject(Save::class, $args);
    }

    /**
     * @return void
     */
    public function testSaveAction(): void
    {
        $userId = 1;
        $requestParams = [
            'password' => 'password',
            'password_confirmation' => true,
            'interface_locale' => 'US',
            'username' => 'Foo',
            'firstname' => 'Bar',
            'lastname' => 'Dummy',
            'email' => 'test@example.com',
            Form::IDENTITY_VERIFICATION_PASSWORD_FIELD => 'current_password'
        ];

        $testedMessage = 'You saved the account.';

        $this->authSessionMock->expects($this->any())->method('getUser')->willReturn($this->userMock);

        $this->userMock->expects($this->any())->method('load')->willReturnSelf();
        $this->validatorMock->expects(
            $this->once()
        )->method(
            'isValid'
        )->with(
            $requestParams['interface_locale']
        )->willReturn(
            true
        );
        $this->managerMock->expects($this->any())->method('switchBackendInterfaceLocale');

        $this->objectManagerMock
            ->method('get')
            ->withConsecutive([Session::class], [Locale::class], [Manager::class])
            ->willReturnOnConsecutiveCalls($this->authSessionMock, $this->validatorMock, $this->managerMock);
        $this->objectManagerMock
            ->method('create')
            ->with(User::class)
            ->willReturn($this->userMock);

        $this->userMock->setUserId($userId);
        $this->userMock->expects($this->once())->method('performIdentityCheck')->willReturn(true);
        $this->userMock->expects($this->once())->method('save');
        $this->userMock->expects($this->once())->method('validate')->willReturn(true);
        $this->userMock->expects($this->once())->method('sendNotificationEmailsIfRequired');

        $this->requestMock->setParams($requestParams);

        $this->messagesMock->expects($this->once())->method('addSuccessMessage')->with($testedMessage);

        $this->controller->execute();
    }
}
