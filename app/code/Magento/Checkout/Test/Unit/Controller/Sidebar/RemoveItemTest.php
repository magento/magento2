<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Sidebar;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Checkout\Model\Sidebar;
use Magento\Checkout\Controller;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Psr\Log\LoggerInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Checkout\Controller\Sidebar\RemoveItem;

/**
 * Class RemoveItemTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveItemTest extends \PHPUnit_Framework_TestCase
{
    /** @var RemoveItem */
    protected $removeItem;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Sidebar|\PHPUnit_Framework_MockObject_MockObject */
    protected $sidebarMock;

    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /** @var Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonHelperMock;

    /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $responseMock;

    /** @var PageFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultPageFactoryMock;

    /** @var Validator|\PHPUnit_Framework_MockObject_MockObject */
    private $formKeyValidatorMock;

    protected function setUp()
    {
        $this->sidebarMock = $this->getMock(Sidebar::class, [], [], '', false);
        $this->loggerMock = $this->getMock(LoggerInterface::class);
        $this->jsonHelperMock = $this->getMock(Data::class, [], [], '', false);
        $this->requestMock = $this->getMock(RequestInterface::class);
        $this->responseMock = $this->getMockForAbstractClass(
            ResponseInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['representJson']
        );
        $this->resultPageFactoryMock =
            $this->getMock(PageFactory::class, [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->removeItem = $this->objectManagerHelper->getObject(
            RemoveItem::class,
            [
                'sidebar' => $this->sidebarMock,
                'logger' => $this->loggerMock,
                'jsonHelper' => $this->jsonHelperMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
            ]
        );

        $this->formKeyValidatorMock =
            $this->getMock(Validator::class, [], [], '', false);

        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->removeItem,
            'formKeyValidator',
            $this->formKeyValidatorMock
        );
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);

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

    public function testExecuteWithValidationLocalizedException()
    {
        $exceptionMessage = 'We can\'t remove the item.';
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        $this->sidebarMock->expects($this->never())
            ->method('checkQuoteItem');

        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with($exceptionMessage)
            ->willReturn(
                [
                    'success' => false,
                    'error_message' => $exceptionMessage,
                ]
            );

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(
                [
                    'success' => false,
                    'error_message' => $exceptionMessage,
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

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);

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

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);

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
