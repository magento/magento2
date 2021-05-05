<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Sidebar;

use Magento\Checkout\Controller\Sidebar\UpdateItemQty;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UpdateItemQtyTest extends TestCase
{
    /** @var UpdateItemQty */
    protected $updateItemQty;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Sidebar|MockObject */
    protected $sidebarMock;

    /** @var LoggerInterface|MockObject */
    protected $loggerMock;

    /** @var Data|MockObject */
    protected $jsonHelperMock;

    /** @var RequestInterface|MockObject */
    protected $requestMock;

    /** @var ResponseInterface|MockObject */
    protected $responseMock;

    /** @var RequestQuantityProcessor|MockObject */
    private $quantityProcessor;

    protected function setUp(): void
    {
        $this->sidebarMock = $this->createMock(Sidebar::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->jsonHelperMock = $this->createMock(Data::class);
        $this->quantityProcessor = $this->createMock(RequestQuantityProcessor::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->responseMock = $this->getMockForAbstractClass(
            ResponseInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['representJson']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->updateItemQty = $this->objectManagerHelper->getObject(
            UpdateItemQty::class,
            [
                'sidebar' => $this->sidebarMock,
                'logger' => $this->loggerMock,
                'jsonHelper' => $this->jsonHelperMock,
                'quantityProcessor' => $this->quantityProcessor,
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

        $this->quantityProcessor->expects($this->once())
            ->method('prepareQuantity')
            ->with(2)
            ->willReturn(2);

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
