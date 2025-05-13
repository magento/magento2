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
use Magento\Checkout\Model\AddProductToCart;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Json\Helper\Data as JsonSerializer;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
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
     * @var ProductRepositoryInterface&MockObject
     */
    private $productRepository;

    /**
     * @var ObjectManagerInterface&MockObject
     */
    private $objectManagerMock;

    /**
     * @var RequestQuantityProcessor&MockObject
     */
    private $quantityProcessor;

    /**
     * @var AddProductToCart&MockObject
     */
    private $addProductToCart;

    /**
     * @var Cart&MockObject
     */
    private $cart;

    /**
     * @var \Magento\Framework\App\Response\Http&MockObject
     */
    private $response;

    /**
     * @var Add|MockObject
     */
    private $cartAdd;

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
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getmock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->quantityProcessor = $this->createMock(RequestQuantityProcessor::class);
        $this->addProductToCart = $this->createMock(AddProductToCart::class);
        $this->cart = $this->createMock(Cart::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cartAdd = $this->objectManagerHelper->getObject(
            Add::class,
            [
                '_formKeyValidator' => $this->formKeyValidator,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
                'productRepository' => $this->productRepository,
                '_objectManager' => $this->objectManagerMock,
                'quantityProcessor' => $this->quantityProcessor,
                'addProductToCart' => $this->addProductToCart,
                'cart' => $this->cart,
                '_response' => $this->response
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecuteWhenFormKeyValidatorFails(): void
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

    public function testExecuteWithValidData(): void
    {
        $productId = 1;
        $storeId = 1;
        $params = ['qty' => 1];
        $product = $this->createMock(Product::class);
        $quote = $this->createMock(Quote::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->createMock(Store::class);
        $localeResolver = $this->createMock(ResolverInterface::class);
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->request->method('getParam')
            ->willReturnMap([
                ['product', null, $productId],
                ['related_product', null, '2,3'],
                ['return_url', null, '/sku.html']
            ]);
        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn($params);
        $this->request->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->willReturn($product);
        $this->objectManagerMock->method('get')
            ->with()
            ->willReturnMap([
                [StoreManagerInterface::class, $storeManager],
                [ResolverInterface::class, $localeResolver],
                [JsonSerializer::class, $this->createMock(JsonSerializer::class)],
            ]);
        $this->addProductToCart->expects($this->once())
            ->method('execute')
            ->with($this->cart, $product, $params, [2, 3])
            ->willReturn(true);
        $this->quantityProcessor->expects($this->once())
            ->method('prepareQuantity')
            ->with($params['qty'])
            ->willReturn($params['qty']);
        $this->cart->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->assertEquals($this->response, $this->cartAdd->execute());
    }
}
