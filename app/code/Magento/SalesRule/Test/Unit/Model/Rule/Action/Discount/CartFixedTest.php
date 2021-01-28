<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Rule\Action\Discount;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\CartExtension;
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
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Tests for Magento\SalesRule\Model\Rule\Action\Discount\CartFixed.
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

        $this->quote = $this->createPartialMock(
            Quote::class,
            [
                'getStore',
                'getCartFixedRules',
                'setCartFixedRules',
                'getExtensionAttributes',
                'isVirtual'
            ]
        );
        $this->address = $this->createPartialMock(
            Address::class,
            ['__wakeup', 'getShippingMethod']
        );
        $this->item->expects($this->any())->method('getQuote')->willReturn($this->quote);
        $this->item->expects($this->any())->method('getAddress')->willReturn($this->address);

        $this->validator = $this->createMock(Validator::class);
        /** @var DataFactory|MockObject $dataFactory */
        $dataFactory = $this->createPartialMock(
            DataFactory::class,
            ['create']
        );
        $dataFactory->expects($this->any())->method('create')->willReturn($this->data);
        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['roundPrice'])
            ->getMockForAbstractClass();
        $this->deltaPriceRound = $this->getMockBuilder(DeltaPriceRound::class)
            ->setMethods(['round'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartFixedDiscountHelper = $this->getMockBuilder(CartFixedDiscount::class)
            ->setMethods([
                'calculateShippingAmountWhenAppliedToShipping',
                'getDiscountAmount',
                'checkMultiShippingQuote',
                'getQuoteTotalsForMultiShipping',
                'getQuoteTotalsForRegularShipping',
                'getBaseRuleTotals',
                'getAvailableDiscountAmount'])
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
            ->willReturn(
                
                    $ruleDetails['id']
                
            );
        $this->rule
            ->expects($this->any())
            ->method('getApplyToShipping')
            ->willReturn(
                
                    $shipping['is_applied_to_shipping']
                
            );
        $this->cartFixedDiscountHelper
            ->expects($this->any())
            ->method('getDiscountAmount')
            ->willReturn(
                
                    $ruleDetails['discounted_amount']
                
            );
        $cartExtensionMock = $this->getMockBuilder(CartExtension::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAssignments'])
            ->getMock();
        $this->quote->expects($this->any())->method('getCartFixedRules')->willReturn([]);
        $store = $this->createMock(Store::class);
        $this->priceCurrency
            ->expects($this->atLeastOnce())
            ->method('convert')
            ->willReturnArgument(
                
                    $ruleDetails['rounded_amount']
                
            );
        $this->priceCurrency
            ->expects($this->atLeastOnce())
            ->method('roundPrice')
            ->willReturnArgument(
                
                    $ruleDetails['rounded_amount']
                
            );
        $this->deltaPriceRound
            ->expects($this->any())
            ->method('round')
            ->willReturn(
                
                    $ruleDetails['base_items_price']
                
            );
        $this->quote->expects($this->any())->method('getStore')->willReturn($store);
        $this->quote->method('isVirtual')
            ->willReturn(false);
        $this->quote->method('getExtensionAttributes')
            ->willReturn($cartExtensionMock);

        $cartExtensionMock->method('getShippingAssignments')
            ->willReturn($shipping['shipping_assignment']);

        $this->address
            ->expects($this->once())
            ->method('getShippingMethod')
            ->willReturn(
                
                    $shipping['shipping_method']
                
            );

        /** validators data */
        $this->validator
            ->expects($this->once())
            ->method('getItemPrice')
            ->with($this->item)
            ->willReturn($ruleDetails['items_price']);
        $this->validator
            ->expects($this->once())
            ->method('getItemBasePrice')
            ->with($this->item)
            ->willReturn($ruleDetails['base_items_price']);
        $this->validator
            ->expects($this->once())
            ->method('getItemOriginalPrice')
            ->with($this->item)
            ->willReturn($ruleDetails['items_price']);
        $this->validator
            ->expects($this->once())
            ->method('getItemBaseOriginalPrice')
            ->with($this->item)
            ->willReturn($ruleDetails['items_price']);
        $this->validator
            ->expects($this->once())
            ->method('getRuleItemTotalsInfo')
            ->with($this->rule->getId())
            ->willReturn($ruleDetails);

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
