<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class SaveTest
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $formKeyValidatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\Save
     */
    protected $controller;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->formKeyValidatorMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultPageFactoryMock);
        $contextMock->expects($this->any())
            ->method('getFormKeyValidator')
            ->willReturn($this->formKeyValidatorMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Invoice\Save::class,
            [
                'context' => $contextMock,
            ]
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecuteNotValidPost()
    {
        $redirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->willReturn(false);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with("The invoice can't be saved at this time. Please try again later.");
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/order/index')
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->controller->execute());
    }
}
