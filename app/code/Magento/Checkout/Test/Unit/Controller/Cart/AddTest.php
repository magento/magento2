<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Controller\Cart\Add;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Validator|MockObject
     */
    private $formKeyValidator;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var Add|MockObject
     */
    private $cartAdd;

    /**
     * @var CustomerCart|MockObject
     */
    private CustomerCart $cart;

    /**
     * @var StoreManagerInterface|StoreManagerInterface&MockObject|MockObject
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Context|Context&MockObject|MockObject
     */
    private Context $context;

    /**
     * @var Session|Session&MockObject|MockObject
     */
    private Session $checkoutSession;

    /**
     * Init mocks for tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory =
            $this->getMockBuilder(RedirectFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['isAjax'])
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cart = $this->createMock(CustomerCart::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->context = $this->createMock(Context::class);
        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getRedirectUrl', 'getUseNotice'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cartAdd = $this->objectManagerHelper->getObject(
            Add::class,
            [
                '_formKeyValidator' => $this->formKeyValidator,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
                'cart' => $this->cart,
                'storeManager' => $this->storeManager,
                'productRepository' => $this->productRepository,
                'context' => $this->context,
                'checkoutSession' => $this->checkoutSession
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $redirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $path = '*/*/';

        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(false);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);
        $redirect->expects($this->once())->method('setPath')->with($path)->willReturnSelf();
        $this->assertEquals($redirect, $this->cartAdd->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithError(): void
    {
        $helper = $this->createMock(Data::class);
        $escaper = $this->createMock(\Magento\Framework\Escaper::class);
        $escaper->expects($this->once())->method('escapeHtml');
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->any())
            ->method('get')
            ->willReturnOnConsecutiveCalls($escaper, $helper);
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($objectManager);
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['representJson'])
            ->getMockForAbstractClass();
        $response->expects($this->once())->method('representJson');
        $this->context->expects($this->any())->method('getResponse')->willReturn($response);
        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(true);
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnOnConsecutiveCalls(1, [], []);
        $this->request->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getId');
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $product = $this->createMock(Product::class);
        $this->productRepository->expects($this->once())->method('getById')->willReturn($product);
        $this->cart->expects($this->once())
            ->method('save')
            ->willThrowException(new LocalizedException(new Phrase('error')));
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $message = $this->createMock(MessageInterface::class);
        $error = $this->createMock(Collection::class);
        $error->expects($this->exactly(2))->method('getErrors')->willReturn([$message]);
        $this->messageManager->expects($this->exactly(2))->method('getMessages')->willReturn($error);
        $this->checkoutSession->expects($this->once())->method('getRedirectUrl')->willReturn('redirect');

        $cartAdd = $this->objectManagerHelper->getObject(
            Add::class,
            [
                '_formKeyValidator' => $this->formKeyValidator,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
                'cart' => $this->cart,
                'storeManager' => $this->storeManager,
                'productRepository' => $this->productRepository,
                'context' => $this->context,
                'checkoutSession' => $this->checkoutSession
            ]
        );

        $cartAdd->execute();
    }
}
