<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Sidebar;

use Magento\Checkout\Controller\Sidebar\UpdateItemQty;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Psr\Log\LoggerInterface;

class UpdateItemQtyTest extends \PHPUnit\Framework\TestCase
{
    /** @var UpdateItemQty */
    private $updateItemQty;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var Sidebar|\PHPUnit_Framework_MockObject_MockObject */
    private $sidebarMock;

    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    /** @var Data|\PHPUnit_Framework_MockObject_MockObject */
    private $jsonHelperMock;

    /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $requestMock;

    /** @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $responseMock;

    protected function setUp()
    {
        $this->sidebarMock = $this->createMock(Sidebar::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->jsonHelperMock = $this->createMock(Data::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
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
                'request' => $this->requestMock,
                'response' => $this->responseMock,
            ]
        );
    }

    /**
     * Tests execute action.
     */
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

    /**
     * Tests with localized exception.
     */
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

    /**
     * Tests with exception.
     */
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

    /**
     * Tests execute with float item quantity.
     */
    public function testExecuteWithFloatItemQty()
    {
        $itemId = '1';
        $floatItemQty = '2.2';

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn($itemId);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('item_qty', null)
            ->willReturn($floatItemQty);

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with($itemId);
        $this->sidebarMock->expects($this->once())
            ->method('updateQuoteItem')
            ->with($itemId, $floatItemQty);

        $this->updateItemQty->execute();
    }
}
