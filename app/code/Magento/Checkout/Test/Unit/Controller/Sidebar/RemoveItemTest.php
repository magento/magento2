<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Sidebar;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveItemTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Checkout\Controller\Sidebar\RemoveItem */
    protected $removeItem;

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

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectFactory;

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
        $this->resultPageFactoryMock = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);
        $this->resultRedirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->removeItem = $this->objectManagerHelper->getObject(
            \Magento\Checkout\Controller\Sidebar\RemoveItem::class,
            [
                'sidebar' => $this->sidebarMock,
                'logger' => $this->loggerMock,
                'jsonHelper' => $this->jsonHelperMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultRedirectFactory' => $this->resultRedirectFactory

            ]
        );
        $formKeyValidatorMock = $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $this->setPropertyValue($this->removeItem, 'formKeyValidator', $formKeyValidatorMock);
    }

    public function testExecute()
    {
        $this->getPropertyValue($this->removeItem, 'formKeyValidator')
            ->expects($this->once())
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
        $this->getPropertyValue($this->removeItem, 'formKeyValidator')
            ->expects($this->once())
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
        $this->getPropertyValue($this->removeItem, 'formKeyValidator')
            ->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
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
        $response = [
            'success' => false,
            'error_message' => $message,
        ];

        $this->getPropertyValue($this->removeItem, 'formKeyValidator')
            ->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        $this->requestMock->expects($this->once())->method('getParam')->with('item_id')->willReturn($itemId);

        $exception = new \Zend_Db_Exception($dbError);

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with($itemId)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

        $this->sidebarMock->expects($this->once())->method('getResponseData')->with($message)->willReturn($response);
        $encodedResponse = json_encode($response);
        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($response)
            ->willReturn($encodedResponse);

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with($encodedResponse)
            ->willReturn($this->responseMock);

        $this->removeItem->execute();
    }

    public function testExecuteWhenFormKeyValidationFailed()
    {
        $resultRedirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $resultRedirect->expects($this->once())->method('setPath')->with('*/cart/')->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->getPropertyValue($this->removeItem, 'formKeyValidator')
            ->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(false);
        $this->assertEquals($resultRedirect, $this->removeItem->execute());
    }

    /**
     * Get any object property value.
     *
     * @param $object
     * @param $property
     * @return mixed
     * @deprecated
     */
    protected function getPropertyValue($object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * Set object property value.
     *
     * @param $object
     * @param $property
     * @param $value
     * @deprecated
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }
}
