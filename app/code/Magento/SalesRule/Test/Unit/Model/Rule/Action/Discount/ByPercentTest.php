<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Action\Discount;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\ByPercent;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ByPercentTest extends TestCase
{
    /**
     * @var ByPercent
     */
    protected $model;

    /**
     * @var Validator|MockObject
     */
    protected $validator;

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

        $this->discountDataFactory = $this->getMockBuilder(
            DataFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['create']
            )->getMock();

        $this->model = $helper->getObject(
            ByPercent::class,
            ['discountDataFactory' => $this->discountDataFactory, 'validator' => $this->validator]
        );
    }

    /**
     * @param $qty
     * @param $ruleData
     * @param $itemData
     * @param $validItemData
     * @param $expectedRuleDiscountQty
     * @param $expectedDiscountData
     * @dataProvider calculateDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCalculate(
        $qty,
        $ruleData,
        $itemData,
        $validItemData,
        $expectedRuleDiscountQty,
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
                ['getDiscountAmount', 'getDiscountQty']
            )->getMock();

        $item = $this->getMockBuilder(
            AbstractItem::class
        )->disableOriginalConstructor()
            ->addMethods(['getDiscountAmount', 'getBaseDiscountAmount',
                'getDiscountPercent', 'setDiscountPercent'])
            ->onlyMethods(
                [
                    'getQuote',
                    'getAddress',
                    'getOptionByCode',
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
            $this->atLeastOnce()
        )->method(
            'getItemBasePrice'
        )->with(
            $item
        )->willReturn(
            $validItemData['basePrice']
        );
        $this->validator->expects(
            $this->atLeastOnce()
        )->method(
            'getItemOriginalPrice'
        )->with(
            $item
        )->willReturn(
            $validItemData['originalPrice']
        );
        $this->validator->expects(
            $this->atLeastOnce()
        )->method(
            'getItemBaseOriginalPrice'
        )->with(
            $item
        )->willReturn(
            $validItemData['baseOriginalPrice']
        );

        $rule->expects(
            $this->atLeastOnce()
        )->method(
            'getDiscountAmount'
        )->willReturn(
            $ruleData['discountAmount']
        );
        $rule->expects(
            $this->atLeastOnce()
        )->method(
            'getDiscountQty'
        )->willReturn(
            $ruleData['discountQty']
        );

        $item->expects(
            $this->atLeastOnce()
        )->method(
            'getDiscountAmount'
        )->willReturn(
            $itemData['discountAmount']
        );
        $item->expects(
            $this->atLeastOnce()
        )->method(
            'getBaseDiscountAmount'
        )->willReturn(
            $itemData['baseDiscountAmount']
        );
        if (!$ruleData['discountQty'] || $ruleData['discountQty'] >= $qty) {
            $item->expects(
                $this->atLeastOnce()
            )->method(
                'getDiscountPercent'
            )->willReturn(
                $itemData['discountPercent']
            );
            $item->expects($this->atLeastOnce())->method('setDiscountPercent')->with($expectedRuleDiscountQty);
        }

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
     * @return array
     */
    public static function calculateDataProvider()
    {
        return [
            [
                'qty' => 3,
                'ruleData' => ['discountAmount' => 30, 'discountQty' => 5],
                'itemData' => ['discountAmount' => 10, 'baseDiscountAmount' => 50, 'discountPercent' => 55],
                'validItemData' => [
                    'price' => 50,
                    'basePrice' => 45,
                    'originalPrice' => 60,
                    'baseOriginalPrice' => 55,
                ],
                'expectedRuleDiscountQty' => 85,
                'expectedDiscountData' => [
                    'amount' => 42,
                    'baseAmount' => 25.5,
                    'originalAmount' => 51,
                    'baseOriginalAmount' => 34.5,
                ],
            ],
            [
                'qty' => 5,
                'ruleData' => ['discountAmount' => 30, 'discountQty' => 5],
                'itemData' => ['discountAmount' => 10, 'baseDiscountAmount' => 50, 'discountPercent' => 55],
                'validItemData' => [
                    'price' => 50,
                    'basePrice' => 45,
                    'originalPrice' => 60,
                    'baseOriginalPrice' => 55,
                ],
                'expectedRuleDiscountQty' => 85,
                'expectedDiscountData' => [
                    'amount' => 72,
                    'baseAmount' => 52.5,
                    'originalAmount' => 87,
                    'baseOriginalAmount' => 67.5,
                ],
            ]
        ];
    }

    /**
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
