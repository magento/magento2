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

/**
 * Class used to execute test cases for update item quantity
 */
class UpdateItemQtyTest extends TestCase
{
    /**
     * @var UpdateItemQty
     */
    protected $updateItemQty;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Sidebar|MockObject
     */
    protected $sidebarMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var Data|MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var RequestQuantityProcessor|MockObject
     */
    private $quantityProcessor;

    /**
     * @inheritDoc
     */
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
                'response' => $this->responseMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['item_id', null], ['item_qty', null])
            ->willReturnOnConsecutiveCalls('1', '2');

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
                        'subtotal' => 12.34
                    ]
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'data' => [
                        'summary_qty' => 2,
                        'summary_text' => __(' items'),
                        'subtotal' => 12.34
                    ]
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

    /**
     * @return void
     */
    public function testExecuteWithLocalizedException(): void
    {
        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['item_id', null], ['item_qty', null])
            ->willReturnOnConsecutiveCalls('1', '2');

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
                    'error_message' => 'Error!'
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'success' => false,
                    'error_message' => 'Error!'
                ]
            )
            ->willReturn('json encoded');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('json encoded')
            ->willReturn('json represented');

        $this->assertEquals('json represented', $this->updateItemQty->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithException(): void
    {
        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['item_id', null], ['item_qty', null])
            ->willReturnOnConsecutiveCalls('1', '2');

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
                    'error_message' => 'Error!'
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'success' => false,
                    'error_message' => 'Error!'
                ]
            )
            ->willReturn('json encoded');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('json encoded')
            ->willReturn('json represented');

        $this->assertEquals('json represented', $this->updateItemQty->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithInvalidItemQty(): void
    {
        $error = [
            'success' => false,
            'error_message' => 'Invalid Item Quantity Requested.'
        ];
        $jsonResult = json_encode($error);
        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['item_id', null], ['item_qty', null])
            ->willReturnOnConsecutiveCalls('1', '{{7+2}}');

        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with('Invalid Item Quantity Requested.')
            ->willReturn($error);

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($error)
            ->willReturn($jsonResult);

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->willReturn($jsonResult);

        $this->assertEquals($jsonResult, $this->updateItemQty->execute());
    }
}
