<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Sidebar;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class UpdateItemQtyTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Checkout\Controller\Sidebar\UpdateItemQty */
    protected $updateItemQty;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Checkout\Model\Sidebar|\PHPUnit\Framework\MockObject\MockObject */
    protected $sidebarMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $loggerMock;

    /** @var \Magento\Framework\Json\Helper\Data|\PHPUnit\Framework\MockObject\MockObject */
    protected $jsonHelperMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $responseMock;

    protected function setUp(): void
    {
        $this->sidebarMock = $this->createMock(\Magento\Checkout\Model\Sidebar::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->jsonHelperMock = $this->createMock(\Magento\Framework\Json\Helper\Data::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->responseMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\ResponseInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['representJson']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->updateItemQty = $this->objectManagerHelper->getObject(
            \Magento\Checkout\Controller\Sidebar\UpdateItemQty::class,
            [
                'sidebar' => $this->sidebarMock,
                'logger' => $this->loggerMock,
                'jsonHelper' => $this->jsonHelperMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
            ]
        );
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('item_qty', null)
            ->willReturn('2');

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with(1)
            ->willReturnSelf();
        $this->sidebarMock->expects($this->once())
            ->method('updateQuoteItem')
            ->with(1, 2)
            ->willReturnSelf();
        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with('')
            ->willReturn(
                [
                    'data' => [
                        'summary_qty' => 2,
                        'summary_text' => __(' items'),
                        'subtotal' => 12.34,
                    ],
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'data' => [
                        'summary_qty' => 2,
                        'summary_text' => __(' items'),
                        'subtotal' => 12.34,
                    ],
                ]
            )
            ->willReturn('json encoded');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('json encoded')
            ->willReturn('json represented');

        $this->assertEquals('json represented', $this->updateItemQty->execute());
    }

    public function testExecuteWithLocalizedException()
    {
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('item_qty', null)
            ->willReturn('2');

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with(1)
            ->willThrowException(new LocalizedException(__('Error!')));

        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with('Error!')
            ->willReturn(
                [
                    'success' => false,
                    'error_message' => 'Error!',
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'success' => false,
                    'error_message' => 'Error!',
                ]
            )
            ->willReturn('json encoded');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('json encoded')
            ->willReturn('json represented');

        $this->assertEquals('json represented', $this->updateItemQty->execute());
    }

    public function testExecuteWithException()
    {
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('item_qty', null)
            ->willReturn('2');

        $exception = new \Exception('Error!');

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
            ->with('Error!')
            ->willReturn(
                [
                    'success' => false,
                    'error_message' => 'Error!',
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'success' => false,
                    'error_message' => 'Error!',
                ]
            )
            ->willReturn('json encoded');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('json encoded')
            ->willReturn('json represented');

        $this->assertEquals('json represented', $this->updateItemQty->execute());
    }
}
