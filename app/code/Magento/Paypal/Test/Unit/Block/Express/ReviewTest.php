<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Block\Express;

use Magento\Paypal\Block\Express\Review;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $assetRepo;

    /**
     * @var Review
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                $this->stringContains('advanced/modules_disable_output/'),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn(false);

        $urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $urlBuilder->expects($this->any())->method('getUrl')->willReturnArgument(0);

        $context = $this->createPartialMock(
            \Magento\Framework\View\Element\Template\Context::class,
            ['getLayout', 'getEventManager', 'getScopeConfig', 'getRequest', 'getAssetRepository', 'getUrlBuilder']
        );

        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->assetRepo = $this->createMock(\Magento\Framework\View\Asset\Repository::class);

        $context->expects($this->any())->method('getLayout')->willReturn($layout);
        $context->expects($this->any())->method('getEventManager')->willReturn($eventManager);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfig);
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $context->expects($this->any())->method('getAssetRepository')->willReturn($this->assetRepo);
        $context->expects($this->any())->method('getUrlBuilder')->willReturn($urlBuilder);

        $this->model = $helper->getObject(\Magento\Paypal\Block\Express\Review::class, ['context' => $context]);
    }

    /**
     * @param bool $isSecure
     * @dataProvider getViewFileUrlDataProvider
     */
    public function testGetViewFileUrl($isSecure)
    {
        $this->request->expects($this->once())->method('isSecure')->willReturn($isSecure);
        $this->assetRepo->expects($this->once())
            ->method('getUrlWithParams')
            ->with('some file', $this->callback(function ($value) use ($isSecure) {
                return isset($value['_secure']) && $value['_secure'] === $isSecure;
            }))
            ->willReturn('result url');
        $this->assertEquals('result url', $this->model->getViewFileUrl('some file'));
    }

    /**
     * @return array
     */
    public function getViewFileUrlDataProvider()
    {
        return [[true], [false]];
    }

    public function testBeforeToHtmlWhenQuoteIsNotVirtual()
    {
        $quote = $this->_getQuoteMock();
        $quote->expects($this->any())->method('getIsVirtual')->willReturn(false);
        $quote->setMayEditShippingMethod('MayEditShippingMethod');

        $shippingRate = new \Magento\Framework\DataObject(['code' => 'Rate 1']);
        $shippingRates = [
            [$shippingRate],
        ];
        $quote->getShippingAddress()
            ->expects($this->any())
            ->method('getGroupedAllShippingRates')
            ->willReturn($shippingRates);
        $quote->getShippingAddress()
            ->expects($this->any())
            ->method('getShippingMethod')
            ->willReturn($shippingRate->getCode());

        $this->model->setQuote($quote);
        $this->model->toHtml();

        $this->assertEquals(
            $this->model->getPaymentMethodTitle(),
            $quote->getPayment()->getMethodInstance()->getTitle()
        );
        $this->assertTrue($this->model->getShippingRateRequired());
        $this->assertSame($shippingRates, $this->model->getShippingRateGroups());
        $this->assertSame($shippingRate, $this->model->getCurrentShippingRate());
        $this->assertNotNull($this->model->getCanEditShippingAddress());
        $this->assertEquals($quote->getMayEditShippingMethod(), $this->model->getCanEditShippingMethod());
        $this->assertStringContainsString(
            'paypal/express/saveShippingMethod',
            $this->model->getShippingMethodSubmitUrl()
        );
        $this->assertStringContainsString('paypal/express/edit', $this->model->getEditUrl());
        $this->assertStringContainsString('paypal/express/placeOrder', $this->model->getPlaceOrderUrl());
    }

    public function testBeforeToHtmlWhenQuoteIsVirtual()
    {
        $quote = $this->_getQuoteMock();
        $quote->expects($this->any())->method('getIsVirtual')->willReturn(true);
        $this->model->setQuote($quote);
        $this->model->toHtml();
        $this->assertEquals(
            $this->model->getPaymentMethodTitle(),
            $quote->getPayment()->getMethodInstance()->getTitle()
        );
        $this->assertFalse($this->model->getShippingRateRequired());
        $this->assertStringContainsString('paypal/express/edit', $this->model->getEditUrl());
        $this->assertStringContainsString('paypal/express/placeOrder', $this->model->getPlaceOrderUrl());
    }

    /**
     * Create mock of sales quote model
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function _getQuoteMock()
    {
        $methodInstance = new \Magento\Framework\DataObject(['title' => 'Payment Method']);
        $payment = $this->createMock(\Magento\Quote\Model\Quote\Payment::class);
        $payment->expects($this->any())->method('getMethodInstance')->willReturn($methodInstance);

        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quote->expects($this->any())->method('getPayment')->willReturn($payment);
        $quote->setPayment($payment);

        $address = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingMethod', 'getGroupedAllShippingRates', '__wakeup'])
            ->getMock();
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($address);

        return $quote;
    }

    public function testGetEmail()
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $billingAddressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $billingAddressMock->expects($this->once())->method('getEmail')->willReturn('test@example.com');
        $this->model->setQuote($quoteMock);
        $this->assertEquals('test@example.com', $this->model->getEmail());
    }

    public function testGetEmailWhenBillingAddressNotExist()
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn(null);
        $this->model->setQuote($quoteMock);
        $this->assertEquals('', $this->model->getEmail());
    }

    public function testCanEditShippingMethod()
    {
        $this->model->setData('can_edit_shipping_method', true);
        static::assertTrue($this->model->canEditShippingMethod());

        $this->model->setData('can_edit_shipping_method', false);
        static::assertTrue($this->model->canEditShippingMethod());
    }
}
