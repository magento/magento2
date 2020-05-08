<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Action\Discount;

use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->rule = $this->getMockBuilder(DataObject::class)
            ->setMockClassName('Rule')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->item = $this->createMock(AbstractItem::class);
        $this->data = $this->createPartialMock(Data::class, []);

        $this->quote = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCartFixedRules', 'setCartFixedRules'])
            ->onlyMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->address = $this->createMock(
            Address::class
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
            ->getMock();
        $deltaPriceRound = $this->getMockBuilder(DeltaPriceRound::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CartFixed(
            $this->validator,
            $dataFactory,
            $this->priceCurrency,
            $deltaPriceRound
        );
    }

    /**
     * @covers \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed::calculate
     */
    public function testCalculate()
    {
        $ruleItemTotals = [
            'items_price' => 100,
            'base_items_price' => 100,
            'items_count' => 1,
        ];

        $this->rule->setData(['id' => 1, 'discount_amount' => 10.0]);

        $this->quote->expects($this->any())->method('getCartFixedRules')->willReturn([]);
        $store = $this->createMock(Store::class);
        $this->priceCurrency->expects($this->atLeastOnce())->method('convert')->willReturnArgument(0);
        $this->priceCurrency->expects($this->atLeastOnce())->method('round')->willReturnArgument(0);
        $this->quote->expects($this->any())->method('getStore')->willReturn($store);

        $this->validator->expects($this->once())
            ->method('getRuleItemTotalsInfo')
            ->with($this->rule->getId())
            ->willReturn($ruleItemTotals);

        /** validators data */
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemPrice'
        )->with(
            $this->item
        )->willReturn(
            100
        );
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemBasePrice'
        )->with(
            $this->item
        )->willReturn(
            100
        );
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemOriginalPrice'
        )->with(
            $this->item
        )->willReturn(
            100
        );
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemBaseOriginalPrice'
        )->with(
            $this->item
        )->willReturn(
            100
        );

        $this->quote->expects($this->once())->method('setCartFixedRules')->with([1 => 0.0]);
        $this->model->calculate($this->rule, $this->item, 1);

        $this->assertEquals($this->data->getAmount(), 10);
        $this->assertEquals($this->data->getBaseAmount(), 10);
        $this->assertEquals($this->data->getOriginalAmount(), 10);
        $this->assertEquals($this->data->getBaseOriginalAmount(), 100);
    }
}
