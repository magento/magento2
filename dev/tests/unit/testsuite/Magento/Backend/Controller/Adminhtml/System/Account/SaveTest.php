<?php
/**
 * Unit test for \Magento\Backend\Controller\Adminhtml\System\Account controller
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Account;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Backend\Controller\Adminhtml\System\Account */
    protected $_controller;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface */
    protected $_requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResponseInterface */
    protected $_responseMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManager\ObjectManager */
    protected $_objectManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Message\ManagerInterface */
    protected $_messagesMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Helper\Data */
    protected $_helperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Auth\Session */
    protected $_authSessionMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\User\Model\User */
    protected $_userMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Locale\Validator */
    protected $_validatorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Locale\Manager */
    protected $_managerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\TranslateInterface */
    protected $_translatorMock;

    protected function setUp()
    {
        $this->_requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->setMethods(['getOriginalPathInfo'])
            ->getMock();
        $this->_responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->_objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $frontControllerMock = $this->getMockBuilder('Magento\Framework\App\FrontController')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_helperMock = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();
        $this->_messagesMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['addSuccess'])
            ->getMockForAbstractClass();

        $this->_authSessionMock = $this->getMockBuilder('Magento\Backend\Model\Auth\Session')
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();

        $this->_userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(
                ['load', 'save', 'sendPasswordResetNotificationEmail', 'verifyIdentity', '__sleep', '__wakeup']
            )
            ->getMock();

        $this->_validatorMock = $this->getMockBuilder('Magento\Framework\Locale\Validator')
            ->disableOriginalConstructor()
            ->setMethods(['isValid'])
            ->getMock();

        $this->_managerMock = $this->getMockBuilder('Magento\Backend\Model\Locale\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['switchBackendInterfaceLocale'])
            ->getMock();

        $this->_translatorMock = $this->getMockBuilder('Magento\Framework\TranslateInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->_requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->_responseMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->_objectManagerMock));
        $contextMock->expects($this->any())
            ->method('getFrontController')
            ->will($this->returnValue($frontControllerMock));

        $contextMock->expects($this->any())->method('getHelper')->will($this->returnValue($this->_helperMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->_messagesMock));
        $contextMock->expects($this->any())->method('getTranslator')->will($this->returnValue($this->_translatorMock));

        $resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirectFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($resultRedirect);

        $args = ['context' => $contextMock, 'resultRedirectFactory' => $resultRedirectFactory];

        $testHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_controller = $testHelper->getObject('Magento\Backend\Controller\Adminhtml\System\Account\Save', $args);
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

        $testedMessage = 'The account has been saved.';

        $this->_authSessionMock->expects($this->any())->method('getUser')->will($this->returnValue($this->_userMock));

        $this->_userMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_validatorMock->expects(
            $this->once()
        )->method(
            'isValid'
        )->with(
            $this->equalTo($requestParams['interface_locale'])
        )->will(
            $this->returnValue(true)
        );
        $this->_managerMock->expects($this->any())->method('switchBackendInterfaceLocale');

        $this->_objectManagerMock->expects(
            $this->at(0)
        )->method(
            'get'
        )->with(
            $this->equalTo('Magento\Backend\Model\Auth\Session')
        )->will(
            $this->returnValue($this->_authSessionMock)
        );
        $this->_objectManagerMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\User\Model\User')
        )->will(
            $this->returnValue($this->_userMock)
        );
        $this->_objectManagerMock->expects(
            $this->at(2)
        )->method(
            'get'
        )->with(
            $this->equalTo('Magento\Framework\Locale\Validator')
        )->will(
            $this->returnValue($this->_validatorMock)
        );
        $this->_objectManagerMock->expects(
            $this->at(3)
        )->method(
            'get'
        )->with(
            $this->equalTo('Magento\Backend\Model\Locale\Manager')
        )->will(
            $this->returnValue($this->_managerMock)
        );

        $this->_userMock->setUserId($userId);

        $this->_userMock->expects($this->once())->method('save');
        $this->_userMock->expects($this->once())->method('verifyIdentity')->will($this->returnValue(true));
        $this->_userMock->expects($this->once())->method('sendPasswordResetNotificationEmail');

        $this->_requestMock->setParams($requestParams);

        $this->_messagesMock->expects($this->once())->method('addSuccess')->with($this->equalTo($testedMessage));

        $this->_controller->execute();
    }
}
