<?php declare(strict_types=1);
/**
 * Unit test for \Magento\Backend\Controller\Adminhtml\System\Account controller
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /** @var Account */
    protected $_controller;

    /** @var MockObject|RequestInterface */
    protected $_requestMock;

    /** @var MockObject|ResponseInterface */
    protected $_responseMock;

    /** @var MockObject|ObjectManager */
    protected $_objectManagerMock;

    /** @var MockObject|\Magento\Framework\Message\ManagerInterface */
    protected $_messagesMock;

    /** @var MockObject|Data */
    protected $_helperMock;

    /** @var MockObject|Session */
    protected $_authSessionMock;

    /** @var MockObject|User */
    protected $_userMock;

    /** @var MockObject|Locale */
    protected $_validatorMock;

    /** @var MockObject|Manager */
    protected $_managerMock;

    /** @var MockObject|TranslateInterface */
    protected $_translatorMock;

    /** @var MockObject|EventManagerInterface */
    protected $eventManagerMock;

    protected function setUp(): void
    {
        $this->_requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOriginalPathInfo'])
            ->getMock();
        $this->_responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->_objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $frontControllerMock = $this->getMockBuilder(FrontController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();
        $this->_messagesMock = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSuccessMessage'])
            ->getMockForAbstractClass();

        $this->_authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();

        $this->_userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load', 'save', 'sendNotificationEmailsIfRequired',
                    'performIdentityCheck', 'validate', '__sleep', '__wakeup'
                ]
            )
            ->getMock();

        $this->_validatorMock = $this->getMockBuilder(Locale::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValid'])
            ->getMock();

        $this->_managerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->setMethods(['switchBackendInterfaceLocale'])
            ->getMock();

        $this->_translatorMock = $this->getMockBuilder(TranslateInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
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
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->_requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->_responseMock);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->_objectManagerMock);
        $contextMock->expects($this->any())->method('getFrontController')->willReturn($frontControllerMock);
        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->_helperMock);
        $contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->_messagesMock);
        $contextMock->expects($this->any())->method('getTranslator')->willReturn($this->_translatorMock);
        $contextMock->expects($this->once())->method('getResultFactory')->willReturn($resultFactory);

        $args = ['context' => $contextMock];

        $testHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_controller = $testHelper->getObject(
            Save::class,
            $args
        );
    }

    public function testSaveAction()
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
            Form::IDENTITY_VERIFICATION_PASSWORD_FIELD => 'current_password',
        ];

        $testedMessage = 'You saved the account.';

        $this->_authSessionMock->expects($this->any())->method('getUser')->willReturn($this->_userMock);

        $this->_userMock->expects($this->any())->method('load')->willReturnSelf();
        $this->_validatorMock->expects(
            $this->once()
        )->method(
            'isValid'
        )->with(
            $requestParams['interface_locale']
        )->willReturn(
            true
        );
        $this->_managerMock->expects($this->any())->method('switchBackendInterfaceLocale');

        $this->_objectManagerMock->expects(
            $this->at(0)
        )->method(
            'get'
        )->with(
            Session::class
        )->willReturn(
            $this->_authSessionMock
        );
        $this->_objectManagerMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            User::class
        )->willReturn(
            $this->_userMock
        );
        $this->_objectManagerMock->expects(
            $this->at(2)
        )->method(
            'get'
        )->with(
            Locale::class
        )->willReturn(
            $this->_validatorMock
        );
        $this->_objectManagerMock->expects(
            $this->at(3)
        )->method(
            'get'
        )->with(
            Manager::class
        )->willReturn(
            $this->_managerMock
        );

        $this->_userMock->setUserId($userId);
        $this->_userMock->expects($this->once())->method('performIdentityCheck')->willReturn(true);
        $this->_userMock->expects($this->once())->method('save');
        $this->_userMock->expects($this->once())->method('validate')->willReturn(true);
        $this->_userMock->expects($this->once())->method('sendNotificationEmailsIfRequired');

        $this->_requestMock->setParams($requestParams);

        $this->_messagesMock->expects($this->once())->method('addSuccessMessage')->with($testedMessage);

        $this->_controller->execute();
    }
}
