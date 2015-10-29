<?php
/**
 * Unit test for \Magento\Backend\Controller\Adminhtml\System\Account controller
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Controller\Adminhtml\System\Account;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Controller\Adminhtml\System\Account\Save
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManager\ObjectManager
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Message\ManagerInterface
     */
    protected $messagesMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Helper\Data
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Auth\Session
     */
    protected $authSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\User\Model\User
     */
    protected $userMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Validator\locale
     */
    protected $validatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Locale\Manager
     */
    protected $managerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\TranslateInterface
     */
    protected $translatorMock;

    protected function setUp()
    {
        $frontControllerMock = $this->getMockBuilder('Magento\Framework\App\FrontController')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->setMethods(['getOriginalPathInfo'])
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $this->helperMock = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();
        $this->messagesMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['addSuccess'])
            ->getMockForAbstractClass();
        $this->authSessionMock = $this->getMockBuilder('Magento\Backend\Model\Auth\Session')
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();
        $this->userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(
                ['load', 'save', 'sendPasswordResetNotificationEmail', 'verifyIdentity', '__sleep', '__wakeup']
            )
            ->getMock();
        $this->validatorMock = $this->getMockBuilder('Magento\Framework\Validator\Locale')
            ->disableOriginalConstructor()
            ->setMethods(['isValid'])
            ->getMock();
        $this->managerMock = $this->getMockBuilder('Magento\Backend\Model\Locale\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['switchBackendInterfaceLocale'])
            ->getMock();
        $this->translatorMock = $this->getMockBuilder('Magento\Framework\TranslateInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactory = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $contextMock = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
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
        $this->controller = $testHelper->getObject('Magento\Backend\Controller\Adminhtml\System\Account\Save', $args);
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
            \Magento\Backend\Block\System\Account\Edit\Form::IDENTITY_VERIFICATION_PASSWORD_FIELD => 'current_password',
        ];

        $testedMessage = __('You saved the account.');

        $this->authSessionMock->expects($this->any())
            ->method('getUser')
            ->willReturn($this->userMock);
        $this->userMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with($this->equalTo($requestParams['interface_locale']))
            ->willReturn(true);
        $this->managerMock->expects($this->any())
            ->method('switchBackendInterfaceLocale');
        $this->objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('Magento\Backend\Model\Auth\Session'))
            ->willReturn($this->authSessionMock);
        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with($this->equalTo('Magento\User\Model\User'))
            ->willReturn($this->userMock);
        $this->objectManagerMock->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('Magento\Framework\Validator\Locale'))
            ->willReturn($this->validatorMock);
        $this->objectManagerMock->expects($this->at(3))
            ->method('get')
            ->with($this->equalTo('Magento\Backend\Model\Locale\Manager'))
            ->willReturn($this->managerMock);
        $this->userMock->expects($this->once())
            ->method('save');
        $this->userMock->expects($this->once())
            ->method('verifyIdentity')
            ->willReturn(true);
        $this->userMock->expects($this->once())
            ->method('sendPasswordResetNotificationEmail');
        $this->messagesMock->expects($this->once())
            ->method('addSuccess')
            ->with($this->equalTo($testedMessage));

        $this->userMock->setUserId($userId);
        $this->requestMock->setParams($requestParams);

        $this->controller->executeInternal();
    }
}
