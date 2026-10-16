<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Checkout\Controller\Cart\CouponPost;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CouponPostTest extends TestCase
{
    /**
     * @var RequestInterface&MockObject
     */
    private $request;

    /**
     * @var ManagerInterface&MockObject
     */
    private $messageManager;

    /**
     * @var Cart&MockObject
     */
    private $cart;

    /**
     * @var CartRepositoryInterface&MockObject
     */
    private $quoteRepository;

    /**
     * @var Redirect&MockObject
     */
    private $redirect;

    /**
     * @var CouponPost
     */
    private $controller;

    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->cart = $this->createMock(Cart::class);
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->redirect = $this->createMock(Redirect::class);
        $redirectFactory = $this->createMock(RedirectFactory::class);
        $redirectFactory->method('create')->willReturn($this->redirect);
        $redirectHelper = $this->createMock(RedirectInterface::class);
        $redirectHelper->method('getRefererUrl')->willReturn('');

        $this->controller = (new ObjectManagerHelper($this))->getObject(
            CouponPost::class,
            [
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $redirectFactory,
                '_redirect' => $redirectHelper,
                'cart' => $this->cart,
                'quoteRepository' => $this->quoteRepository,
            ]
        );
    }

    /**
     * @param mixed $couponCode
     * @return void
     */
    #[DataProvider('nonStringCouponCodeProvider')]
    public function testExecuteRejectsNonStringCouponCode(mixed $couponCode): void
    {
        $this->request->method('getParam')
            ->willReturnMap([
                ['remove', null, null],
                ['coupon_code', '', $couponCode],
            ]);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->messageManager->expects($this->never())->method('addSuccessMessage');
        $this->cart->expects($this->never())->method('getQuote');
        $this->quoteRepository->expects($this->never())->method('save');

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @return array
     */
    public static function nonStringCouponCodeProvider(): array
    {
        return [
            'array' => [['x']],
            'nested array' => [[['x']]],
            'empty array' => [[]],
        ];
    }
}
