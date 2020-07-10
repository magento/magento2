<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Controller\Billing\Agreement;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Paypal\Controller\Billing\Agreement\Cancel;
use Magento\Paypal\Model\Billing\Agreement as BillingAgreement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CancelTest extends TestCase
{
    /**
     * @var \Magento\Paypal\Controller\Billing\Agreement
     */
    protected $_controller;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $_objectManager;

    /**
     * @var RequestInterface|MockObject
     */
    protected $_request;

    /**
     * @var Registry|MockObject
     */
    protected $_registry;

    /**
     * @var Session|MockObject
     */
    protected $_session;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $_messageManager;

    /**
     * @var BillingAgreement|MockObject
     */
    protected $_agreement;

    protected function setUp(): void
    {
        $this->_session = $this->createMock(Session::class);

        $this->_agreement = $this->getMockBuilder(BillingAgreement::class)
            ->addMethods(['getCustomerId', 'getReferenceId'])
            ->onlyMethods(['load', 'getId', 'canCancel', 'cancel', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_agreement->expects($this->once())->method('load')->with(15)->willReturnSelf();
        $this->_agreement->expects($this->once())->method('getId')->willReturn(15);
        $this->_agreement->expects($this->once())->method('getCustomerId')->willReturn(871);

        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'get'
        )->willReturnMap(
            [[Session::class, $this->_session]]
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            BillingAgreement::class
        )->willReturn(
            $this->_agreement
        );

        $this->_request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->_request->expects($this->once())->method('getParam')->with('agreement')->willReturn(15);

        $response = $this->getMockForAbstractClass(ResponseInterface::class);

        $redirect = $this->getMockForAbstractClass(RedirectInterface::class);

        $this->_messageManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $context = $this->createMock(Context::class);
        $context->expects($this->any())->method('getObjectManager')->willReturn($this->_objectManager);
        $context->expects($this->any())->method('getRequest')->willReturn($this->_request);
        $context->expects($this->any())->method('getResponse')->willReturn($response);
        $context->expects($this->any())->method('getRedirect')->willReturn($redirect);
        $context->expects($this->any())->method('getMessageManager')->willReturn($this->_messageManager);

        $this->_registry = $this->createMock(Registry::class);

        $this->_controller = new Cancel(
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
