<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type\Multishipping;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\Plugin;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var MockObject
     */
    private $cartMock;

    /**
     * @var Plugin
     */
    private $model;

    /**
     * @var MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $localeMock;

    /**
     * @var MockObject
     */
    private $quantityProcessorMock;

    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(
                [
                    'getCheckoutState',
                    'setCheckoutState',
                    'getMultiShippingAddressesFlag',
                    'setMultiShippingAddressesFlag'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quantityProcessorMock = $this->getMockBuilder(RequestQuantityProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartMock = $this->createMock(Cart::class);
        $this->model = new Plugin(
            $this->checkoutSessionMock,
            $this->cartRepositoryMock,
            $this->requestMock,
            $this->localeMock,
            $this->quantityProcessorMock
        );
    }

    public function testBeforeInitCaseTrue()
    {
        $this->checkoutSessionMock->expects($this->once())->method('getCheckoutState')
            ->willReturn(State::STEP_SELECT_ADDRESSES);
        $this->checkoutSessionMock->expects($this->once())->method('setCheckoutState')
            ->with(Session::CHECKOUT_STATE_BEGIN);
        $this->model->beforeSave($this->cartMock);
    }

    public function testBeforeInitCaseFalse()
    {
        $this->checkoutSessionMock->expects($this->once())->method('getCheckoutState')
            ->willReturn('');
        $this->checkoutSessionMock->expects($this->never())->method('setCheckoutState');
        $this->model->beforeSave($this->cartMock);
    }

    /**
     * Test cart plugin after save method
     *
     * @param float $itemInitialQuantity
     * @param array $params
     * @param bool $multipleShippingAddressesFlag
     * @dataProvider getDataDataProvider
     */
    public function testAfterSave(
        float $itemInitialQuantity,
        array $params,
        bool $multipleShippingAddressesFlag
    ): void {
        $defaultLocale = 'en_US';
        $this->localeMock->method('getLocale')
            ->willReturn($defaultLocale);
        $this->cartMock->method('getCheckoutSession')
            ->willReturn($this->checkoutSessionMock);
        $this->checkoutSessionMock->method('getMultiShippingAddressesFlag')
            ->willReturn($multipleShippingAddressesFlag);
        $this->checkoutSessionMock->method('setMultiShippingAddressesFlag')
            ->willReturn(!$multipleShippingAddressesFlag);
        $this->requestMock->method('getParams')
            ->willReturn($params);

        $quoteMock = $this->createPartialMock(Quote::class, [
            'getItemsQty',
            'setItemsQty',
            'collectTotals'
        ]);

        $this->cartMock->method('getQuote')
            ->willReturn($quoteMock);
        $quoteMock->method('getItemsQty')
            ->willReturn($itemInitialQuantity);
        $this->quantityProcessorMock->method('prepareQuantity')
            ->willReturn($params['qty']);
        $quoteMock->method('setItemsQty')
            ->willReturnSelf();
        $quoteMock->method('collectTotals')
            ->willReturnSelf();
        $this->cartRepositoryMock->method('save')
            ->with($quoteMock)
            ->willReturnSelf();
        $this->model->afterSave($this->cartMock, $this->cartMock);
    }

    /**
     * @return array
     */
    public static function getDataDataProvider()
    {
        return [
            'test with multi shipping addresses' => [10.0, ['qty' => '5'], true],
            'test without multi shipping addresses' => [10.0, ['qty' => '5'], false],
        ];
    }
}
