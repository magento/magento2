<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Action\Discount;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\ByFixed;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ByFixedTest extends TestCase
{
    /**
     * @var ByFixed
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

        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMockForAbstractClass();

        $this->discountDataFactory = $this->getMockBuilder(
            DataFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['create']
            )->getMock();

        $this->model = $helper->getObject(
            ByFixed::class,
            [
                'discountDataFactory' => $this->discountDataFactory,
                'validator' => $this->validator,
                'priceCurrency' => $this->priceCurrency
            ]
        );
    }

    /**
     * Test fixed discount cannot be higher than products price
     *
     * @param $qty
     * @param $ruleData
     * @param $itemData
     * @param $validItemData
     * @param $expectedDiscountData
     * @dataProvider calculateDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
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

        $rule = $this->getMockBuilder(
            Rule::class
        )->disableOriginalConstructor()
            ->addMethods(
                ['getDiscountAmount']
            )->getMock();

        $quote = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->createMock(Store::class);
        $quote->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $item = $this->getMockBuilder(
            AbstractItem::class
        )->disableOriginalConstructor()
            ->addMethods(['getDiscountAmount', 'getBaseDiscountAmount',])
            ->onlyMethods(
                [
                    'getQuote',
                    'getAddress',
                    'getOptionByCode',
                    'getQty'
                ]
            )->getMock();
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

        $this->priceCurrency->expects(
            $this->any()
        )->method(
            'convert'
        )->with(
            $ruleData['discountAmount'],
            $store
        )->willReturn(
            $ruleData['discountAmount']
        );

        $this->setUpMockData($ruleData, $rule);
        $this->setUpMockData($itemData, $item);
        $item->expects($this->atLeastOnce())->method('getQuote')->willReturn($quote);

        $discountData->expects($this->once())->method('setAmount')->with($expectedDiscountData['amount']);
        $discountData->expects($this->once())->method('setBaseAmount')->with($expectedDiscountData['baseAmount']);
        $discountData->expects(
            $this->any()
        )->method(
            'setOriginalAmount'
        )->with(
            $expectedDiscountData['originalAmount']
        );
        $discountData->expects(
            $this->any()
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
            $mockObject->expects($this->atLeastOnce())
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
            [
                'qty' => 2,
                'ruleData' => ['discountAmount' => 100],
                'itemData' => ['discountAmount' => 139, 'baseDiscountAmount' => 139, 'qty' => 2],
                'validItemData' => [
                    'price' => 139,
                    'basePrice' => 139,
                    'originalPrice' => 139,
                    'baseOriginalPrice' => 139,
                ],
                'expectedDiscountData' => [
                    'amount' => 139,
                    'baseAmount' => 139,
                    'originalAmount' => 0,
                    'baseOriginalAmount' => 0,
                ],
            ],
            [
                'qty' => 1,
                'ruleData' => ['discountAmount' => 1000],
                'itemData' => ['discountAmount' => 9100, 'baseDiscountAmount' => 9100, 'qty' => 13],
                'validItemData' => [
                    'price' => 9000,
                    'basePrice' => 9000,
                    'originalPrice' => 9000,
                    'baseOriginalPrice' => 9000,
                ],
                'expectedDiscountData' => [
                    'amount' => 1000,
                    'baseAmount' => 1000,
                    'originalAmount' => 0,
                    'baseOriginalAmount' => 0,
                ],
            ]
        ];
    }

    /**
     * Test Fixing quantity depending on discount step
     *
     * @param int $step
     * @param int|float $qty
     * @param int $expected
     * @dataProvider fixQuantityDataProvider
     */
    public function testFixQuantity($step, $qty, $expected)
    {
        $rule = $this->getMockBuilder(Rule::class)
            ->addMethods(['getDiscountStep'])
            ->disableOriginalConstructor()
            ->getMock();
        $rule->expects($this->once())->method('getDiscountStep')->willReturn($step);

        $this->assertEquals($expected, $this->model->fixQuantity($qty, $rule));
    }

    /**
     * @return array
     */
    public static function fixQuantityDataProvider()
    {
        return [
            ['step' => 0, 'qty' => 23, 'expected' => 23],
            ['step' => 10, 'qty' => 23.5, 'expected' => 20],
            ['step' => 20, 'qty' => 33, 'expected' => 20],
            ['step' => 25, 'qty' => 23, 'expected' => 0]
        ];
    }
}
