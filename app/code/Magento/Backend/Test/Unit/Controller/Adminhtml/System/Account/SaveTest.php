<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as HelperObjectManager;
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
    use MockCreationTrait;

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
     * @var MockObject|MessageManagerInterface
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
     * @var HelperObjectManager
     */
    private $objectManagerHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new HelperObjectManager($this);
        $this->requestMock = $this->createPartialMock(Http::class, ['getOriginalPathInfo']);
        $this->responseMock = $this->createMock(ResponseHttp::class);
        $this->objectManagerMock = $this->createPartialMock(ObjectManager::class, ['get', 'create']);
        $frontControllerMock = $this->createMock(FrontController::class);

        $this->helperMock = $this->createPartialMock(Data::class, ['getUrl']);
        $this->messagesMock = $this->createPartialMock(
            MessageManager::class,
            ['addSuccessMessage']
        );

        $this->authSessionMock = $this->createPartialMockWithReflection(
            Session::class,
            ['getUser']
        );

        $this->userMock = $this->createPartialMock(
            User::class,
            [
                'load',
                'save',
                'sendNotificationEmailsIfRequired',
                'performIdentityCheck',
                'validate',
                '__sleep',
                '__wakeup'
            ]
        );

        $this->validatorMock = $this->createPartialMock(Locale::class, ['isValid']);

        $this->managerMock = $this->createPartialMock(Manager::class, ['switchBackendInterfaceLocale']);

        $this->translatorMock = $this->createMock(TranslateInterface::class);

        $resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $resultRedirect = $this->createMock(Redirect::class);
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $contextMock = $this->createPartialMockWithReflection(
            Context::class,
            [
                'getFrontController',
                'getTranslator',
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getHelper',
                'getMessageManager',
                'getResultFactory'
            ]
        );
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())->method('getFrontController')->willReturn($frontControllerMock);
        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->helperMock);
        $contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messagesMock);
        $contextMock->expects($this->any())->method('getTranslator')->willReturn($this->translatorMock);
        $contextMock->expects($this->once())->method('getResultFactory')->willReturn($resultFactory);

        $args = ['context' => $contextMock];

        $this->controller = $this->objectManagerHelper->getObject(Save::class, $args);
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

        $testedMessage = "The password, username, firstname, lastname and email of this account"
            ." have been modified successfully.";

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
            ->willReturnCallback(fn($param) => match ([$param]) {
                [Session::class] => $this->authSessionMock,
                [Locale::class] => $this->validatorMock,
                [Manager::class] => $this->managerMock
            });
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
