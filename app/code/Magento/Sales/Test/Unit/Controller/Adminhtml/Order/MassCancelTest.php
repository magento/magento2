<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class MassCancelTest
 */
class MassCancelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\MassCancel
     */
    protected $massAction;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->contextMock = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getRequest',
                'getResponse',
                'getMessageManager',
                'getRedirect',
                'getObjectManager',
                'getSession',
                'getActionFlag',
                'getHelper',
                'getResultRedirectFactory'
            ],
            [],
            '',
            false
        );
        $resultRedirectFactory = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->responseMock = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            ['create'],
            [],
            '',
            false
        );
        $this->messageManagerMock = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addSuccess', 'addError'],
            [],
            '',
            false
        );
        $this->orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        );
        $this->sessionMock = $this->getMock('Magento\Backend\Model\Session', ['setIsUrlNotice'], [], '', false);
        $this->actionFlagMock = $this->getMock('Magento\Framework\App\ActionFlag', ['get', 'set'], [], '', false);
        $this->helperMock = $this->getMock('\Magento\Backend\Helper\Data', ['getUrl'], [], '', false);
        $this->resultRedirectMock = $this->getMock('Magento\Backend\Model\View\Result\Redirect', [], [], '', false);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirectMock);

        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())->method('getSession')->willReturn($this->sessionMock);
        $this->contextMock->expects($this->once())->method('getActionFlag')->willReturn($this->actionFlagMock);
        $this->contextMock->expects($this->once())->method('getHelper')->willReturn($this->helperMock);
        $this->contextMock->expects($this->once())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);

        $this->massAction = $objectManagerHelper->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\MassCancel',
            [
                'context' => $this->contextMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock
            ]
        );
    }

    public function testExecuteTwoOrderCanceled()
    {
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('selected', [])
            ->willReturn([1, 2]);
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnMap(
                [
                    [1, null, $this->orderMock],
                    [2, null, $this->orderMock],
                ]
            );
        $this->orderMock->expects($this->exactly(2))
            ->method('canCancel')
            ->willReturn(true);
        $this->orderMock->expects($this->exactly(2))
            ->method('cancel')
            ->willReturnSelf();
        $this->orderMock->expects($this->exactly(2))
            ->method('save')
            ->willReturnSelf();
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with('We canceled 2 order(s).');
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/')
            ->willReturnSelf();
        $this->massAction->execute();
    }

    public function testExecuteOneOrderCannotBeCanceled()
    {
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('selected', [])
            ->willReturn([1, 2]);
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnMap(
                [
                    [1, null, $this->orderMock],
                    [2, null, $this->orderMock],
                ]
            );
        $this->orderMock->expects($this->at(1))
            ->method('canCancel')
            ->willReturn(true);
        $this->orderMock->expects($this->at(2))
            ->method('canCancel')
            ->willReturn(false);
        $this->orderMock->expects($this->exactly(1))
            ->method('cancel')
            ->willReturnSelf();
        $this->orderMock->expects($this->exactly(1))
            ->method('save')
            ->willReturnSelf();
        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('1 order(s) cannot be canceled.');
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/')
            ->willReturnSelf();
        $this->massAction->execute();
    }

    public function testExecuteCannotCancelEachOrders()
    {
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('selected', [])
            ->willReturn([1, 2]);
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnMap(
                [
                    [1, null, $this->orderMock],
                    [2, null, $this->orderMock],
                ]
            );
        $this->orderMock->expects($this->exactly(2))
            ->method('canCancel')
            ->willReturn(false);
        $this->orderMock->expects($this->never())
            ->method('cancel')
            ->willReturnSelf();
        $this->orderMock->expects($this->never())
            ->method('save')
            ->willReturnSelf();
        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('You cannot cancel the order(s).');
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/')
            ->willReturnSelf();
        $this->massAction->execute();
    }
}
