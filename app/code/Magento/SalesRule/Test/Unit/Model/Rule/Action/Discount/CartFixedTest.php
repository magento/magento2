<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Rule\Action\Discount;

use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests for Magento\SalesRule\Model\Rule\Action\Discount\CartFixed.
 */
class CartFixedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rule|MockObject
     */
    protected $rule;

    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem|MockObject
     */
    protected $item;

    /**
     * @var \Magento\SalesRule\Model\Validator|MockObject
     */
    protected $validator;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\Data|MockObject
     */
    protected $data;

    /**
     * @var \Magento\Quote\Model\Quote|MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Model\Quote\Address|MockObject
     */
    protected $address;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->rule = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMockClassName('Rule')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->item = $this->createMock(\Magento\Quote\Model\Quote\Item\AbstractItem::class);
        $this->data = $this->createPartialMock(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class, []);

        $this->quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->address = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getCartFixedRules', 'setCartFixedRules', '__wakeup']
        );
        $this->item->expects($this->any())->method('getQuote')->will($this->returnValue($this->quote));
        $this->item->expects($this->any())->method('getAddress')->will($this->returnValue($this->address));

        $this->validator = $this->createMock(\Magento\SalesRule\Model\Validator::class);
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory|MockObject $dataFactory */
        $dataFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory::class,
            ['create']
        );
        $dataFactory->expects($this->any())->method('create')->will($this->returnValue($this->data));
        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMock();
        $deltaPriceRound = $this->getMockBuilder(\Magento\SalesRule\Model\DeltaPriceRound::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceCurrency = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        )->getMock();
        $this->model = new \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed(
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
        $this->rule->setData(['id' => 1, 'discount_amount' => 10.0]);

        $this->address->expects($this->any())->method('getCartFixedRules')->will($this->returnValue([]));
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->priceCurrency->expects($this->atLeastOnce())->method('convert')->will($this->returnArgument(0));
        $this->priceCurrency->expects($this->atLeastOnce())->method('round')->will($this->returnArgument(0));
        $this->quote->expects($this->any())->method('getStore')->will($this->returnValue($store));

        /** validators data */
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemPrice'
        )->with(
            $this->item
        )->will(
            $this->returnValue(100)
        );
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemBasePrice'
        )->with(
            $this->item
        )->will(
            $this->returnValue(100)
        );
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemOriginalPrice'
        )->with(
            $this->item
        )->will(
            $this->returnValue(100)
        );
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemBaseOriginalPrice'
        )->with(
            $this->item
        )->will(
            $this->returnValue(100)
        );

        $this->address->expects($this->once())->method('setCartFixedRules')->with([1 => 0.0]);
        $this->model->calculate($this->rule, $this->item, 1);

        $this->assertEquals($this->data->getAmount(), 10);
        $this->assertEquals($this->data->getBaseAmount(), 10);
        $this->assertEquals($this->data->getOriginalAmount(), 10);
        $this->assertEquals($this->data->getBaseOriginalAmount(), 100);
    }
}
