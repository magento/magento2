<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\Carrier;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflineShipping\Model\Carrier\Freeshipping;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * Class for test free shipping
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FreeshippingTest extends TestCase
{
    /**
     * @var Freeshipping
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var MethodFactory|MockObject
     */
    private $methodFactoryMock;

    /**
     * @var ObjectManager
     */
    private $helper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->methodFactoryMock = $this
            ->getMockBuilder(MethodFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->helper = new ObjectManager($this);
        $this->model = $this->helper->getObject(
            Freeshipping::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                '_rateResultFactory' => $this->resultFactoryMock,
                '_rateMethodFactory' => $this->methodFactoryMock,
            ]
        );
    }

    /**
     * Test for collect rate free shipping with tax options
     *
     * @param int $subtotalInclTax
     * @param int $minOrderAmount
     * @param int $packageValueWithDiscount
     * @param int $baseSubtotalWithDiscountInclTax
     * @param InvokedCount $expectedCallAppend
     *
     * @return void
     * @dataProvider freeShippingWithSubtotalTaxDataProvider
     */
    public function testCollectRatesFreeShippingWithTaxOptions(
        int $subtotalInclTax,
        int $minOrderAmount,
        int $packageValueWithDiscount,
        int $baseSubtotalWithDiscountInclTax,
        InvokedCount $expectedCallAppend
    ): void {
        /** @var RateRequest|MockObject $request */
        $request = $this->getMockBuilder(RateRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getAllItems',
                    'getPackageQty',
                    'getFreeShipping',
                    'getBaseSubtotalWithDiscountInclTax',
                    'getPackageValueWithDiscount',
                ]
            )
            ->getMock();
        $item = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock->expects($this->at(0))
            ->method('isSetFlag')
            ->willReturn(true);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('isSetFlag')
            ->with(
                'carriers/freeshipping/tax_including',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($subtotalInclTax);
        $this->scopeConfigMock->expects($this->at(2))
            ->method('getValue')
            ->with(
                'carriers/freeshipping/free_shipping_subtotal',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($minOrderAmount);
        $method = $this->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setMethod', 'setMethodTitle', 'setPrice', 'setCost'])
            ->getMock();
        $resultModel = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['append'])
            ->getMock();
        $this->resultFactoryMock->method('create')
            ->willReturn($resultModel);
        $request->method('getPackageValueWithDiscount')
            ->willReturn($packageValueWithDiscount);
        $request->method('getAllItems')
            ->willReturn([$item]);
        $request->method('getFreeShipping')
            ->willReturn(false);
        $request->method('getBaseSubtotalWithDiscountInclTax')
            ->willReturn($baseSubtotalWithDiscountInclTax);
        $this->methodFactoryMock->method('create')->willReturn($method);

        $resultModel->expects($expectedCallAppend)
            ->method('append')
            ->with($method);

        $this->model->collectRates($request);
    }

    /**
     * @return array
     */
    public function freeShippingWithSubtotalTaxDataProvider(): array
    {
        return [
            [
                'subtotalInclTax' => 1,
                'minOrderAmount' => 10,
                'packageValueWithDiscount' => 8,
                'baseSubtotalWithDiscountInclTax' => 15,
                'expectedCallAppend' => $this->once(),

            ],
            [
                'subtotalInclTax' => 1,
                'minOrderAmount' => 20,
                'packageValueWithDiscount' => 8,
                'baseSubtotalWithDiscountInclTax' => 15,
                'expectedCallAppend' => $this->never(),

            ],
            [
                'subtotalInclTax' => 0,
                'minOrderAmount' => 10,
                'packageValueWithDiscount' => 8,
                'baseSubtotalWithDiscountInclTax' => 15,
                'expectedCallAppend' => $this->never(),

            ],
        ];
    }
}
