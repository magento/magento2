<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Sidebar;

use Magento\Checkout\Controller\Sidebar\RemoveItem;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveItemTest extends TestCase
{
    /**
     * @var RemoveItem
     */
    private $action;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResultJsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var ResultRedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Sidebar|MockObject
     */
    private $sidebarMock;

    /**
     * @var Validator|MockObject
     */
    private $formKeyValidatorMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultJsonFactoryMock = $this->createPartialMock(
            ResultJsonFactory::class,
            ['create']
        );
        $this->resultRedirectFactoryMock = $this->createPartialMock(
            ResultRedirectFactory::class,
            ['create']
        );
        $this->sidebarMock = $this->createMock(Sidebar::class);
        $this->formKeyValidatorMock = $this->createMock(Validator::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->action = $objectManager->getObject(
            RemoveItem::class,
            [
                'request' => $this->requestMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'sidebar' => $this->sidebarMock,
                'formKeyValidator' => $this->formKeyValidatorMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testExecute()
    {
        $responseData = [
            'cleanup' => true,
            'data' => [
                'summary_qty' => 0,
                'summary_text' => __(' items'),
                'subtotal' => 0,
            ],
        ];

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
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
            ->willReturn($responseData);

        $resultJson = $this->createMock(ResultJson::class);
        $resultJson->expects($this->once())
            ->method('setData')
            ->with($responseData)
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->assertEquals($resultJson, $this->action->execute());
    }

    public function testExecuteWithLocalizedException()
    {
        $errorMessage = 'Error message!';
        $responseData = [
            'success' => false,
            'error_message' => $errorMessage
        ];

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with(1)
            ->willThrowException(new LocalizedException(__($errorMessage)));

        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with($errorMessage)
            ->willReturn($responseData);

        $resultJson = $this->createMock(ResultJson::class);
        $resultJson->expects($this->once())
            ->method('setData')
            ->with($responseData)
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->assertEquals($resultJson, $this->action->execute());
    }

    public function testExecuteWithException()
    {
        $errorMessage = 'Error message!';
        $responseData = [
            'success' => false,
            'error_message' => $errorMessage
        ];

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');

        $exception = new \Exception($errorMessage);

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
            ->with($errorMessage)
            ->willReturn($responseData);

        $resultJson = $this->createMock(ResultJson::class);
        $resultJson->expects($this->once())
            ->method('setData')
            ->with($responseData)
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->assertEquals($resultJson, $this->action->execute());
    }

    /**
     * Test controller when DB exception is thrown.
     *
     * @return void
     */
    public function testExecuteWithDbException(): void
    {
        $itemId = 1;
        $dbError = 'Error';
        $message = __('An unspecified error occurred. Please contact us for assistance.');
        $responseData = [
            'success' => false,
            'error_message' => $message,
        ];

        $this->formKeyValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item_id')
            ->willReturn($itemId);

        $exception = new \Zend_Db_Exception($dbError);

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with($itemId)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with($message)
            ->willReturn($responseData);

        $resultJson = $this->createMock(ResultJson::class);
        $resultJson->expects($this->once())
            ->method('setData')
            ->with($responseData)
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->action->execute();
    }

    public function testExecuteWhenFormKeyValidationFailed()
    {
        $resultRedirect = $this->createMock(ResultRedirect::class);
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/cart/')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(false);

        $this->assertEquals($resultRedirect, $this->action->execute());
    }
}
