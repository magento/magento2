<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Catalog\Helper\Product\View;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Controller\Cart\Configure;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Shopping cart edit tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigureTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var Configure|MockObject
     */
    protected $configureController;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Cart|MockObject
     */
    protected $cartMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->cartMock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $objectManagerHelper = new ObjectManager($this);

        $this->configureController = $objectManagerHelper->getObject(
            Configure::class,
            [
                'context' => $this->contextMock,
                'cart' => $this->cartMock
            ]
        );
    }

    /**
     * Test checks controller call product view and send parameter to it.
     *
     * @return void
     */
    public function testPrepareAndRenderCall(): void
    {
        $quoteId = 1;
        $actualProductId = 1;
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $buyRequestMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        //expects
        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['id'], ['product_id'])
            ->willReturnOnConsecutiveCalls($quoteId, $actualProductId);
        $this->cartMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);

        $quoteItemMock->expects($this->exactly(1))->method('getBuyRequest')->willReturn($buyRequestMock);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE, [])
            ->willReturn($pageMock);
        $this->objectManagerMock
            ->method('get')
            ->with(View::class)
            ->willReturn($viewMock);

        $viewMock->expects($this->once())->method('prepareAndRender')->with(
            $pageMock,
            $actualProductId,
            $this->configureController,
            $this->callback(
                function ($subject) use ($buyRequestMock) {
                    return $subject->getBuyRequest() === $buyRequestMock;
                }
            )
        )->willReturn($pageMock);

        $quoteMock->expects($this->once())->method('getItemById')->willReturn($quoteItemMock);
        $quoteItemMock->expects($this->exactly(2))->method('getProduct')->willReturn($productMock);

        $productMock->expects($this->exactly(2))->method('getId')->willReturn($actualProductId);

        $this->assertSame($pageMock, $this->configureController->execute());
    }

    /**
     * Test checks controller redirect user to cart
     * if user request product id in cart edit page is not same as quota product id.
     *
     * @return void
     */
    public function testRedirectWithWrongProductId(): void
    {
        $quotaId = 1;
        $productIdInQuota = 1;
        $productIdInRequest = null;
        $quoteItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', null, $quotaId],
                ['product_id', null, $productIdInRequest]
            ]);
        $this->cartMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getItemById')->willReturn($quoteItemMock);
        $quoteItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $productMock->expects($this->once())->method('getId')->willReturn($productIdInQuota);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->willReturn('');
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart', [])
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);
        $this->assertSame($this->resultRedirectMock, $this->configureController->execute());
    }
}
