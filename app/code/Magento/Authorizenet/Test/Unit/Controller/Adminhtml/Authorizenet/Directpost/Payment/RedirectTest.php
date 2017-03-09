<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Controller\Adminhtml\Authorizenet\Directpost\Payment;

use Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment\Redirect;
use Magento\Payment\Block\Transparent\Iframe;

/**
 * Class RedirectTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorizenet\Model\Directpost
     */
    protected $directpost;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directpostSessionMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adminOrderCreateMock;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionQuoteMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productHelperMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $forwardFactoryMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultLayoutFactoryMock;

    /**
     * @var \Magento\Authorizenet\Helper\Backend\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    protected function setUp()
    {
        $this->directpostSessionMock = $this->getMockBuilder(\Magento\Authorizenet\Model\Directpost\Session::class)
            ->setMethods([
                    'getLastOrderIncrementId',
                    'removeCheckoutOrderIncrementId',
                    'isCheckoutOrderIncrementIdExist',
                    'unsetData'
                ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getId', 'getState', 'getIncrementId', 'registerCancellation', 'loadByIncrementId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->adminOrderCreateMock = $this->getMockBuilder(\Magento\Sales\Model\AdminOrder\Create::class)
            ->setMethods(['getSession'])
            ->disableOriginalConstructor()
            ->getMock();
        $sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionQuoteMock = $this->getMockBuilder(\Magento\Backend\Model\Session\Quote::class)
            ->setMethods(['getOrder', 'clearStorage'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                [\Magento\Authorizenet\Model\Directpost\Session::class, $this->directpostSessionMock],
                [\Magento\Sales\Model\AdminOrder\Create::class, $this->adminOrderCreateMock],
                [\Magento\Backend\Model\Session\Quote::class, $this->sessionQuoteMock],
                [\Magento\Backend\Model\Session::class, $sessionMock],
            ]);
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->with(\Magento\Sales\Model\Order::class)
            ->willReturn($this->orderMock);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParams'])
            ->getMockForAbstractClass();
        $responseMock = $this->getMockForAbstractClass(\Magento\Framework\App\ResponseInterface::class);
        $redirectMock = $this->getMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->messageManagerMock = $this->getMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->setMethods(['getObjectManager', 'getRequest', 'getResponse', 'getRedirect', 'getMessageManager'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($responseMock);
        $this->contextMock->expects($this->any())->method('getRedirect')->willReturn($redirectMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->productHelperMock = $this->getMockBuilder(\Magento\Catalog\Helper\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\ForwardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->coreRegistryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->setMethods(['register'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultLayoutMock = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultLayoutFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\LayoutFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultLayoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultLayoutMock);
        $this->helperMock = $this->getMockBuilder(\Magento\Authorizenet\Helper\Backend\Data::class)
            ->setMethods(['getSuccessOrderUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new Redirect(
            $this->contextMock,
            $this->productHelperMock,
            $this->escaperMock,
            $this->resultPageFactoryMock,
            $this->forwardFactoryMock,
            $this->coreRegistryMock,
            $this->resultLayoutFactoryMock,
            $this->helperMock
        );
    }

    public function testExecuteErrorMsgWithoutCancelOrder()
    {
        $params = ['success' => 0, 'error_msg' => 'Error message'];
        $incrementId = 1;
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);
        $this->directpostSessionMock->expects($this->once())
            ->method('getLastOrderIncrementId')
            ->willReturn($incrementId);
        $this->directpostSessionMock->expects($this->once())
            ->method('isCheckoutOrderIncrementIdExist')
            ->with($incrementId)
            ->willReturn(true);

        $this->orderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($incrementId)
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->orderMock->expects($this->once())
            ->method('getIncrementId')
            ->willReturn($incrementId);
        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $this->orderMock->expects($this->once())
            ->method('registerCancellation')
            ->with($params['error_msg'])
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('save');

        $this->directpostSessionMock->expects($this->once())
            ->method('removeCheckoutOrderIncrementId')
            ->with($incrementId);
        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with(Iframe::REGISTRY_KEY);

        $this->assertInstanceOf(\Magento\Framework\View\Result\Layout::class, $this->controller->execute());
    }

    public function testExecuteErrorMsgWithCancelOrder()
    {
        $params = ['success' => 0, 'error_msg' => 'Error message', 'x_invoice_num' => 1];
        $incrementId = 1;
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);
        $this->directpostSessionMock->expects($this->once())
            ->method('getLastOrderIncrementId')
            ->willReturn($incrementId);
        $this->directpostSessionMock->expects($this->once())
            ->method('isCheckoutOrderIncrementIdExist')
            ->with($incrementId)
            ->willReturn(true);
        $this->orderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($incrementId)
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->orderMock->expects($this->once())
            ->method('getIncrementId')
            ->willReturn($incrementId);
        $this->directpostSessionMock->expects($this->once())
            ->method('removeCheckoutOrderIncrementId')
            ->with($incrementId);

        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with(Iframe::REGISTRY_KEY);

        $this->assertInstanceOf(\Magento\Framework\View\Result\Layout::class, $this->controller->execute());
    }

    public function testExecuteSuccess()
    {
        $params = ['success' => 1, 'controller_action_name' => 'action', 'x_invoice_num' => 1];
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);

        $this->helperMock->expects($this->once())
            ->method('getSuccessOrderUrl')
            ->willReturn('redirect_parent_url');

        $this->directpostSessionMock->expects($this->once())
            ->method('unsetData')
            ->with('quote_id');

        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->sessionQuoteMock->expects($this->atLeastOnce())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->adminOrderCreateMock->expects($this->atLeastOnce())
            ->method('getSession')
            ->willReturn($this->sessionQuoteMock);

        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with(Iframe::REGISTRY_KEY);

        $this->assertInstanceOf(\Magento\Framework\View\Result\Layout::class, $this->controller->execute());
    }
}
