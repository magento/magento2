<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Sidebar;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RemoveItemTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Checkout\Controller\Sidebar\RemoveItem */
    protected $removeItem;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Checkout\Model\Sidebar|\PHPUnit_Framework_MockObject_MockObject */
    protected $sidebarMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /** @var \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonHelperMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $responseMock;

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultPageFactoryMock;

    protected function setUp()
    {
        $this->sidebarMock = $this->getMock('Magento\Checkout\Model\Sidebar', [], [], '', false);
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->jsonHelperMock = $this->getMock('Magento\Framework\Json\Helper\Data', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->responseMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\ResponseInterface',
            [],
            '',
            false,
            true,
            true,
            ['representJson']
        );
        $this->resultPageFactoryMock = $this->getMock('Magento\Framework\View\Result\PageFactory', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->removeItem = $this->objectManagerHelper->getObject(
            'Magento\Checkout\Controller\Sidebar\RemoveItem',
            [
                'sidebar' => $this->sidebarMock,
                'logger' => $this->loggerMock,
                'jsonHelper' => $this->jsonHelperMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
            ]
        );
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with(1)
            ->willReturnSelf();
        $this->sidebarMock->expects($this->once())
            ->method('removeQuoteItem')
            ->with(1)
            ->willReturnSelf();
        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with('')
            ->willReturn(
                [
                    'cleanup' => true,
                    'data' => [
                        'summary_qty' => 0,
                        'summary_text' => __(' items'),
                        'subtotal' => 0,
                    ],
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'cleanup' => true,
                    'data' => [
                        'summary_qty' => 0,
                        'summary_text' => __(' items'),
                        'subtotal' => 0,
                    ],
                ]
            )
            ->willReturn('json encoded');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('json encoded')
            ->willReturn('json represented');

        $this->assertEquals('json represented', $this->removeItem->execute());
    }

    public function testExecuteWithLocalizedException()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with(1)
            ->willThrowException(new LocalizedException(__('Error message!')));

        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with('Error message!')
            ->willReturn(
                [
                    'success' => false,
                    'error_message' => 'Error message!',
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'success' => false,
                    'error_message' => 'Error message!',
                ]
            )
            ->willReturn('json encoded');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('json encoded')
            ->willReturn('json represented');

        $this->assertEquals('json represented', $this->removeItem->execute());
    }

    public function testExecuteWithException()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');

        $exception = new \Exception('Error message!');

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with(1)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->willReturn(null);

        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with('Error message!')
            ->willReturn(
                [
                    'success' => false,
                    'error_message' => 'Error message!',
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'success' => false,
                    'error_message' => 'Error message!',
                ]
            )
            ->willReturn('json encoded');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('json encoded')
            ->willReturn('json represented');

        $this->assertEquals('json represented', $this->removeItem->execute());
    }
}
