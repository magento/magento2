<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Express;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Block\Express\Review;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Payment;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReviewTest extends TestCase
{
    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var Repository|MockObject
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
        $helper = new ObjectManager($this);

        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                $this->stringContains('advanced/modules_disable_output/'),
                ScopeInterface::SCOPE_STORE
            )->willReturn(false);

        $urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $urlBuilder->expects($this->any())->method('getUrl')->willReturnArgument(0);

        $context = $this->createPartialMock(
            Context::class,
            ['getLayout', 'getEventManager', 'getScopeConfig', 'getRequest', 'getAssetRepository', 'getUrlBuilder']
        );

        $this->request = $this->createMock(Http::class);
        $this->assetRepo = $this->createMock(Repository::class);

        $context->expects($this->any())->method('getLayout')->willReturn($layout);
        $context->expects($this->any())->method('getEventManager')->willReturn($eventManager);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfig);
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $context->expects($this->any())->method('getAssetRepository')->willReturn($this->assetRepo);
        $context->expects($this->any())->method('getUrlBuilder')->willReturn($urlBuilder);

        $this->model = $helper->getObject(Review::class, ['context' => $context]);
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
            ->with(
                'some file',
                $this->callback(
                    function ($value) use ($isSecure) {
                        return isset($value['_secure']) && $value['_secure'] === $isSecure;
                    }
                )
            )
            ->willReturn('result url');
        $this->assertEquals('result url', $this->model->getViewFileUrl('some file'));
    }

    /**
     * @return array
     */
    public static function getViewFileUrlDataProvider()
    {
        return [[true], [false]];
    }

    public function testBeforeToHtmlWhenQuoteIsNotVirtual()
    {
        $quote = $this->_getQuoteMock();
        $quote->expects($this->any())->method('getIsVirtual')->willReturn(false);
        $quote->setMayEditShippingMethod('MayEditShippingMethod');

        $shippingRate = new DataObject(['code' => 'Rate 1']);
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
     * @return MockObject
     */
    protected function _getQuoteMock()
    {
        $methodInstance = new DataObject(['title' => 'Payment Method']);
        $payment = $this->createMock(Payment::class);
        $payment->expects($this->any())->method('getMethodInstance')->willReturn($methodInstance);

        $quote = $this->createMock(Quote::class);
        $quote->expects($this->any())->method('getPayment')->willReturn($payment);
        $quote->setPayment($payment);

        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getShippingMethod', 'getGroupedAllShippingRates', '__wakeup'])
            ->getMock();
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($address);

        return $quote;
    }

    public function testGetEmail()
    {
        $quoteMock = $this->createMock(Quote::class);
        $billingAddressMock = $this->createMock(Address::class);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $billingAddressMock->expects($this->once())->method('getEmail')->willReturn('test@example.com');
        $this->model->setQuote($quoteMock);
        $this->assertEquals('test@example.com', $this->model->getEmail());
    }

    public function testGetEmailWhenBillingAddressNotExist()
    {
        $quoteMock = $this->createMock(Quote::class);
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
