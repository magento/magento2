<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Action\Discount;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\CartFixed;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Magento\SalesRule\Model\Rule\Action\Discount\CartFixed.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartFixedTest extends TestCase
{
    /**
     * @var Rule|MockObject
     */
    protected $rule;

    /**
     * @var AbstractItem|MockObject
     */
    protected $item;

    /**
     * @var Validator|MockObject
     */
    protected $validator;

    /**
     * @var Data|MockObject
     */
    protected $data;

    /**
     * @var Quote|MockObject
     */
    protected $quote;

    /**
     * @var Address|MockObject
     */
    protected $address;

    /**
     * @var CartFixed
     */
    protected $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    /**
     * @var DeltaPriceRound|MockObject
     */
    protected $deltaPriceRound;

    /**
     * @var CartFixedDiscount|MockObject
     */
    protected $cartFixedDiscountHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->rule = $this->getMockBuilder(Rule::class)
            ->setMethods(['getId', 'getApplyToShipping'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->item = $this->createMock(AbstractItem::class);
        $this->data = $this->createPartialMock(Data::class, []);

        $this->quote = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCartFixedRules', 'setCartFixedRules'])
            ->onlyMethods(['getStore', 'getExtensionAttributes', 'isVirtual'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->address = $this->getMockBuilder(Address::class)
            ->onlyMethods(['getShippingMethod'])
            ->addMethods(['getShippingInclTax', 'getShippingExclTax'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->item->expects($this->any())->method('getQuote')->willReturn($this->quote);
        $this->item->expects($this->any())->method('getAddress')->willReturn($this->address);

        $this->validator = $this->createMock(Validator::class);
        /** @var DataFactory|MockObject $dataFactory */
        $dataFactory = $this->createPartialMock(
            DataFactory::class,
            ['create']
        );
        $dataFactory->method('create')->willReturn($this->data);
        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['roundPrice'])
            ->getMockForAbstractClass();
        $this->deltaPriceRound = $this->getMockBuilder(DeltaPriceRound::class)
            ->setMethods(['round'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartFixedDiscountHelper = $this->getMockBuilder(CartFixedDiscount::class)
            ->onlyMethods([
                'calculateShippingAmountWhenAppliedToShipping',
                'getDiscountAmount',
                'getDiscountedAmountProportionally',
                'checkMultiShippingQuote',
                'getQuoteTotalsForMultiShipping',
                'getQuoteTotalsForRegularShipping',
                'getBaseRuleTotals',
                'getAvailableDiscountAmount',
                'applyDiscountOnPricesIncludedTax'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CartFixed(
            $this->validator,
            $dataFactory,
            $this->priceCurrency,
            $this->deltaPriceRound,
            $this->cartFixedDiscountHelper
        );
    }

    /**
     * @covers \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed::calculate
     * @dataProvider dataProviderActions
     * @param array $shipping
     * @param array $ruleDetails
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCalculate(array $shipping, array $ruleDetails): void
    {
        $this->rule->setData(['id' => $ruleDetails['id'], 'discount_amount' => $ruleDetails['discounted_amount']]);
        $this->rule
            ->expects($this->any())
            ->method('getId')
            ->will(
                $this->returnValue(
                    $ruleDetails['id']
                )
            );
        $this->rule
            ->expects($this->any())
            ->method('getApplyToShipping')
            ->will(
                $this->returnValue(
                    $shipping['is_applied_to_shipping']
                )
            );
        $this->cartFixedDiscountHelper
            ->expects($this->any())
            ->method('getDiscountedAmountProportionally')
            ->will(
                $this->returnValue(
                    $ruleDetails['discounted_amount']
                )
            );
        $this->cartFixedDiscountHelper->expects($this->any())
            ->method('applyDiscountOnPricesIncludedTax')
            ->willReturn(true);
        $cartExtensionMock = $this->getMockBuilder(CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAssignments'])
            ->getMockForAbstractClass();
        $this->quote->expects($this->any())->method('getCartFixedRules')->will($this->returnValue([]));
        $store = $this->createMock(Store::class);
        $this->priceCurrency
            ->expects($this->atLeastOnce())
            ->method('convert')
            ->willReturnArgument($ruleDetails['rounded_amount']);
        $this->priceCurrency
            ->expects($this->atLeastOnce())
            ->method('roundPrice')
            ->willReturnArgument($ruleDetails['rounded_amount']);
        $this->deltaPriceRound
            ->expects($this->any())
            ->method('round')
            ->willReturnArgument($ruleDetails['base_items_price']);
        $this->quote->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->quote->method('isVirtual')
            ->willReturn(false);
        $this->quote->method('getExtensionAttributes')
            ->willReturn($cartExtensionMock);

        $cartExtensionMock->method('getShippingAssignments')
            ->willReturn($shipping['shipping_assignment']);

        $this->address
            ->expects($this->once())
            ->method('getShippingMethod')
            ->will(
                $this->returnValue(
                    $shipping['shipping_method']
                )
            );
        $this->address->expects($this->any())
            ->method('getShippingInclTax')
            ->willReturn(15.00);
        $this->address->expects($this->any())
            ->method('getShippingExclTax')
            ->willReturn(10.00);

        /** validators data */
        $this->validator
            ->expects($this->once())
            ->method('getItemPrice')
            ->with($this->item)
            ->will($this->returnValue($ruleDetails['items_price']));
        $this->validator
            ->expects($this->once())
            ->method('getItemBasePrice')
            ->with($this->item)
            ->will($this->returnValue($ruleDetails['base_items_price']));
        $this->validator
            ->expects($this->once())
            ->method('getItemOriginalPrice')
            ->with($this->item)
            ->will($this->returnValue($ruleDetails['items_price']));
        $this->validator
            ->expects($this->once())
            ->method('getItemBaseOriginalPrice')
            ->with($this->item)
            ->will($this->returnValue($ruleDetails['items_price']));
        $this->validator
            ->expects($this->once())
            ->method('getRuleItemTotalsInfo')
            ->with($this->rule->getId())
            ->will($this->returnValue($ruleDetails));

        $this->quote->expects($this->once())->method('setCartFixedRules')->with([1 => $ruleDetails['cart_rules']]);
        $this->model->calculate($this->rule, $this->item, $ruleDetails['items_count']);

        $this->assertEquals($this->data->getAmount(), $ruleDetails['base_items_price']);
        $this->assertEquals($this->data->getBaseAmount(), $ruleDetails['base_items_price']);
        $this->assertEquals($this->data->getOriginalAmount(), $ruleDetails['base_items_price']);
        $this->assertEquals($this->data->getBaseOriginalAmount(), $ruleDetails['items_price']);
    }

    /**
     * @return array
     */
    public static function dataProviderActions()
    {
        return [
            'regular shipping with single item and single shipping' => [
                [
                    'shipping_method' => 'flatrate_flatrate',
                    'is_applied_to_shipping' => 0,
                    'shipping_assignment' => ['test_assignment_1']
                ],
                [   'id' => 1,
                    'base_items_price' => 10.0,
                    'items_price' => 100.0,
                    'items_count' => 1,
                    'rounded_amount' => 0.0,
                    'discounted_amount' => 10.0,
                    'cart_rules' => 0.0
                ]
            ],
            'regular shipping with two items and single shipping' => [
                [
                    'shipping_method' => 'flatrate_flatrate',
                    'is_applied_to_shipping' => 0,
                    'shipping_assignment' => ['test_assignment_1']
                ],
                [   'id' => 1,
                    'base_items_price' => 10.0,
                    'items_price' => 100.0,
                    'items_count' => 2,
                    'rounded_amount' => 0.0,
                    'discounted_amount' => 10.0,
                    'cart_rules' => 0.0
                ]
            ],
            'regular shipping with two items and multiple shipping' => [
                [
                    'shipping_method' => 'flatrate_flatrate',
                    'is_applied_to_shipping' => 0,
                    'shipping_assignment' => ['test_assignment_1', 'test_assignment_2']
                ],
                [   'id' => 1,
                    'base_items_price' => 10.0,
                    'items_price' => 200.0,
                    'items_count' => 2,
                    'rounded_amount' => 0.0,
                    'discounted_amount' => 10.0,
                    'cart_rules' => 0.0
                ]
            ]

        ];
    }
}
