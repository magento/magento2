<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Controller\Billing\Agreement;

class CancelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Paypal\Controller\Billing\Agreement
     */
    protected $_controller;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_registry;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_messageManager;

    /**
     * @var \Magento\Paypal\Model\Billing\Agreement|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_agreement;

    protected function setUp(): void
    {
        $this->_session = $this->createMock(\Magento\Customer\Model\Session::class);

        $this->_agreement = $this->createPartialMock(
            \Magento\Paypal\Model\Billing\Agreement::class,
            ['load', 'getId', 'getCustomerId', 'getReferenceId', 'canCancel', 'cancel', '__wakeup']
        );
        $this->_agreement->expects($this->once())->method('load')->with(15)->willReturnSelf();
        $this->_agreement->expects($this->once())->method('getId')->willReturn(15);
        $this->_agreement->expects($this->once())->method('getCustomerId')->willReturn(871);

        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'get'
        )->willReturnMap(
            [[\Magento\Customer\Model\Session::class, $this->_session]]
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Paypal\Model\Billing\Agreement::class
        )->willReturn(
            $this->_agreement
        );

        $this->_request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->_request->expects($this->once())->method('getParam')->with('agreement')->willReturn(15);

        $response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);

        $redirect = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);

        $this->_messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $context = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $context->expects($this->any())->method('getObjectManager')->willReturn($this->_objectManager);
        $context->expects($this->any())->method('getRequest')->willReturn($this->_request);
        $context->expects($this->any())->method('getResponse')->willReturn($response);
        $context->expects($this->any())->method('getRedirect')->willReturn($redirect);
        $context->expects($this->any())->method('getMessageManager')->willReturn($this->_messageManager);

        $this->_registry = $this->createMock(\Magento\Framework\Registry::class);

        $this->_controller = new \Magento\Paypal\Controller\Billing\Agreement\Cancel(
            $context,
            $this->_registry
        );
    }

    public function testExecuteActionSuccess()
    {
        $this->_agreement->expects($this->once())->method('getReferenceId')->willReturn('r15');
        $this->_agreement->expects($this->once())->method('canCancel')->willReturn(true);
        $this->_agreement->expects($this->once())->method('cancel');

        $noticeMessage = 'The billing agreement "r15" has been canceled.';
        $this->_session->expects($this->once())->method('getCustomerId')->willReturn(871);
        $this->_messageManager->expects($this->once())->method('addNoticeMessage')->with($noticeMessage);
        $this->_messageManager->expects($this->never())->method('addErrorMessage');

        $this->_registry->expects(
            $this->once()
        )->method(
            'register'
        )->with(
            'current_billing_agreement',
            $this->identicalTo($this->_agreement)
        );

        $this->_controller->execute();
    }

    public function testExecuteAgreementDoesNotBelongToCustomer()
    {
        $this->_agreement->expects($this->never())->method('canCancel');
        $this->_agreement->expects($this->never())->method('cancel');

        $errorMessage = 'Please specify the correct billing agreement ID and try again.';
        $this->_session->expects($this->once())->method('getCustomerId')->willReturn(938);
        $this->_messageManager->expects($this->once())->method('addErrorMessage')->with($errorMessage);

        $this->_registry->expects($this->never())->method('register');

        $this->_controller->execute();
    }

    public function testExecuteAgreementStatusDoesNotAllowToCancel()
    {
        $this->_agreement->expects($this->once())->method('canCancel')->willReturn(false);
        $this->_agreement->expects($this->never())->method('cancel');

        $this->_session->expects($this->once())->method('getCustomerId')->willReturn(871);
        $this->_messageManager->expects($this->never())->method('addNoticeMessage');
        $this->_messageManager->expects($this->never())->method('addErrorMessage');

        $this->_registry->expects(
            $this->once()
        )->method(
            'register'
        )->with(
            'current_billing_agreement',
            $this->identicalTo($this->_agreement)
        );

        $this->_controller->execute();
    }
}
