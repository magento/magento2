<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Action\Discount;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\BuyXGetY;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuyXGetYTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var BuyXGetY
     */
    protected $model;

    /**
     * @var Validator|MockObject
     */
    protected $validator;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    /**
     * @var DataFactory|MockObject
     */
    protected $discountDataFactory;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->validator = $this->getMockBuilder(
            Validator::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['getItemPrice', 'getItemBasePrice', 'getItemOriginalPrice', 'getItemBaseOriginalPrice']
            )->getMock();

        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);

        $this->discountDataFactory = $this->getMockBuilder(
            DataFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['create']
            )->getMock();

        $this->model = $helper->getObject(
            BuyXGetY::class,
            [
                'discountDataFactory' => $this->discountDataFactory,
                'validator' => $this->validator,
                'priceCurrency' => $this->priceCurrency
            ]
        );
    }

    /**
     * Test Buy X Get Y Free logic, especially when Y > X
     *
     * @param float $qty
     * @param array $ruleData
     * @param array $itemData
     * @param array $validItemData
     * @param array $expectedDiscountData
     */
    #[DataProvider('calculateDataProvider')]
    public function testCalculate(
        $qty,
        $ruleData,
        $itemData,
        $validItemData,
        $expectedDiscountData
    ) {
        $discountData = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['setAmount', 'setBaseAmount', 'setOriginalAmount', 'setBaseOriginalAmount']
            )->getMock();

        $this->discountDataFactory->expects($this->once())->method('create')->willReturn($discountData);

        $rule = $this->createPartialMockWithReflection(
            Rule::class,
            ['getDiscountAmount', 'getDiscountStep']
        );

        $quote = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->createMock(Store::class);
        $quote->expects($this->any())->method('getStore')->willReturn($store);

        $item = $this->createPartialMockWithReflection(
            AbstractItem::class,
            [
                'getDiscountAmount',
                'getBaseDiscountAmount',
                'getQuote',
                'getAddress',
                'getOptionByCode',
                'getQty'
            ]
        );
        $this->validator->expects(
            $this->atLeastOnce()
        )->method(
            'getItemPrice'
        )->with(
            $item
        )->willReturn(
            $validItemData['price']
        );
        $this->validator->expects(
            $this->any()
        )->method(
            'getItemBasePrice'
        )->with(
            $item
        )->willReturn(
            $validItemData['basePrice']
        );
        $this->validator->expects(
            $this->any()
        )->method(
            'getItemOriginalPrice'
        )->with(
            $item
        )->willReturn(
            $validItemData['originalPrice']
        );
        $this->validator->expects(
            $this->any()
        )->method(
            'getItemBaseOriginalPrice'
        )->with(
            $item
        )->willReturn(
            $validItemData['baseOriginalPrice']
        );

        $this->setUpMockData($ruleData, $rule);
        $this->setUpMockData($itemData, $item);

        $discountData->expects($this->once())->method('setAmount')->with($expectedDiscountData['amount']);
        $discountData->expects($this->once())->method('setBaseAmount')->with($expectedDiscountData['baseAmount']);
        $discountData->expects(
            $this->once()
        )->method(
            'setOriginalAmount'
        )->with(
            $expectedDiscountData['originalAmount']
        );
        $discountData->expects(
            $this->once()
        )->method(
            'setBaseOriginalAmount'
        )->with(
            $expectedDiscountData['baseOriginalAmount']
        );

        $this->assertEquals($discountData, $this->model->calculate($rule, $item, $qty));
    }

    /**
     * Sets up mock object data
     *
     * @param array $data
     * @param MockObject $mockObject
     * @return void
     */
    private function setUpMockData(array $data, MockObject $mockObject): void
    {
        foreach ($data as $method => $returnValue) {
            $mockObject->expects($this->any())
                ->method('get' . ucfirst($method))
                ->willReturn($returnValue);
        }
    }

    /**
     * @return array
     */
    public static function calculateDataProvider()
    {
        return [
            // Standard case: Buy 2 get 1 free. Qty = 3. Should discount 1.
            [
                'qty' => 3,
                'ruleData' => ['discountStep' => 2, 'discountAmount' => 1],
                'itemData' => ['discountAmount' => 0, 'baseDiscountAmount' => 0, 'qty' => 3],
                'validItemData' => [
                    'price' => 10,
                    'basePrice' => 10,
                    'originalPrice' => 10,
                    'baseOriginalPrice' => 10,
                ],
                'expectedDiscountData' => [
                    'amount' => 10,
                    'baseAmount' => 10,
                    'originalAmount' => 10,
                    'baseOriginalAmount' => 10,
                ],
            ],
            // Bug reproduced case: Buy 1 get 5 free (Y > X). Qty = 6. Should discount 5.
            [
                'qty' => 6,
                'ruleData' => ['discountStep' => 1, 'discountAmount' => 5],
                'itemData' => ['discountAmount' => 0, 'baseDiscountAmount' => 0, 'qty' => 6],
                'validItemData' => [
                    'price' => 20,
                    'basePrice' => 20,
                    'originalPrice' => 20,
                    'baseOriginalPrice' => 20,
                ],
                'expectedDiscountData' => [
                    'amount' => 100, // 5 * 20
                    'baseAmount' => 100,
                    'originalAmount' => 100,
                    'baseOriginalAmount' => 100,
                ],
            ],
            // Bug reproduced case partial: Buy 1 get 5 free. Qty = 4. Should discount 3.
            [
                'qty' => 4,
                'ruleData' => ['discountStep' => 1, 'discountAmount' => 5],
                'itemData' => ['discountAmount' => 0, 'baseDiscountAmount' => 0, 'qty' => 4],
                'validItemData' => [
                    'price' => 20,
                    'basePrice' => 20,
                    'originalPrice' => 20,
                    'baseOriginalPrice' => 20,
                ],
                'expectedDiscountData' => [
                    'amount' => 60, // 3 * 20
                    'baseAmount' => 60,
                    'originalAmount' => 60,
                    'baseOriginalAmount' => 60,
                ],
            ],
            // Bug reproduced case partial: Buy 1 get 5 free. Qty = 1. Should discount 0.
            [
                'qty' => 1,
                'ruleData' => ['discountStep' => 1, 'discountAmount' => 5],
                'itemData' => ['discountAmount' => 0, 'baseDiscountAmount' => 0, 'qty' => 1],
                'validItemData' => [
                    'price' => 20,
                    'basePrice' => 20,
                    'originalPrice' => 20,
                    'baseOriginalPrice' => 20,
                ],
                'expectedDiscountData' => [
                    'amount' => 0,
                    'baseAmount' => 0,
                    'originalAmount' => 0,
                    'baseOriginalAmount' => 0,
                ],
            ],
        ];
    }
}
