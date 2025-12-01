<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Checkout\Controller\Cart\UpdatePost;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for UpdatePost controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdatePostTest extends TestCase
{
    /**
     * @var UpdatePost
     */
    private $controller;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var RequestQuantityProcessor|MockObject
     */
    private $quantityProcessorMock;

    /**
     * @var Cart|MockObject
     */
    private $cartMock;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSessionMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirectMock;

    /**
     * @inheritDoc
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        // Create mocks
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->quantityProcessorMock = $this->createMock(RequestQuantityProcessor::class);
        $this->cartMock = $this->createMock(Cart::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->redirectMock = $this->createMock(RedirectInterface::class);

        // Setup context mock
        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getRequest')->willReturn($this->requestMock);
        $contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);

        // Setup Escaper mock
        $this->escaperMock->method('escapeHtml')->willReturn('Test error message');
        $this->objectManagerMock->method('get')
            ->with(Escaper::class)
            ->willReturn($this->escaperMock);
        $this->redirectMock->method('getRefererUrl')->willReturn('http://example.com/cart');

        $defaultFormKeyValidator = $this->createMock(FormKeyValidator::class);
        $defaultFormKeyValidator->method('validate')->willReturn(true);

        $this->controller = $this->createController($contextMock, $defaultFormKeyValidator);
    }

    /**
     * Create controller instance with required dependencies injected
     *
     * @param Context $context
     * @param FormKeyValidator $formKeyValidator
     * @param RedirectFactory|null $redirectFactory
     * @return UpdatePost
     * @throws \ReflectionException
     */
    private function createController(
        Context $context,
        FormKeyValidator $formKeyValidator,
        ?RedirectFactory $redirectFactory = null
    ): UpdatePost {
        if ($redirectFactory === null) {
            $redirectFactory = $this->createMock(RedirectFactory::class);
            $redirectFactory->method('create')->willReturn($this->createMock(Redirect::class));
        }

        $context->method('getResultRedirectFactory')->willReturn($redirectFactory);

        $controller = new UpdatePost(
            $context,
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(CheckoutSession::class),
            $this->createMock(StoreManagerInterface::class),
            $formKeyValidator,
            $this->cartMock,
            $this->quantityProcessorMock
        );

        $reflection = new \ReflectionClass($controller);
        $parent = $reflection->getParentClass();

        $objectManagerProperty = $parent->getProperty('_objectManager');
        $objectManagerProperty->setValue($controller, $this->objectManagerMock);

        $redirectProperty = $parent->getProperty('_redirect');
        $redirectProperty->setValue($controller, $this->redirectMock);

        return $controller;
    }

    /**
     * Test that messageManager->addErrorMessage() is never called in the catch block
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testCatchBlockDoesNotAddErrorMessage(): void
    {
        // Setup request to trigger update_qty action and return cart data
        $this->requestMock->method('getParam')
            ->willReturnMap([
                ['update_cart_action', null, 'update_qty'],
                ['cart', null, ['1' => ['qty' => 10]]]
            ]);

        // Setup form key validation to pass
        $formKeyValidatorMock = $this->createMock(FormKeyValidator::class);
        $formKeyValidatorMock->method('validate')->willReturn(true);

        // Recreate controller with test-specific dependencies
        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getRequest')->willReturn($this->requestMock);
        $contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->controller = $this->createController($contextMock, $formKeyValidatorMock);

        // Setup quantity processor to throw LocalizedException
        $this->quantityProcessorMock->method('process')
            ->willThrowException(new LocalizedException(__('Test error message')));

        // Setup cart methods
        $quoteMock = $this->createMock(Quote::class);
        $this->cartMock->method('getCustomerSession')->willReturn($this->customerSessionMock);
        $this->cartMock->method('getQuote')->willReturn($quoteMock);
        $this->cartMock->method('suggestItemsQty')->willReturn(['1' => ['qty' => 10]]);
        $this->cartMock->method('updateItems')->willReturnSelf();
        $this->cartMock->method('save')->willReturnSelf();

        // Setup customer session mock
        $this->customerSessionMock->method('getCustomerId')->willReturn(null);

        // Verify that messageManager->addErrorMessage() is NEVER called
        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage');

        // Call the public execute() method - this will trigger the _updateShoppingCart logic
        $this->controller->execute();
    }
}
