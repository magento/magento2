<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Magento\SalesRule\Model\Rule\Action\Discount\CartFixed.
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

        $this->quote = $this->createPartialMock(
            Quote::class,
            [
                'getStore',
                'getCartFixedRules',
                'setCartFixedRules',
            ]
        );
        $this->address = $this->createPartialMock(
            Address::class,
            ['__wakeup']
        );
        $this->item->expects($this->any())->method('getQuote')->will($this->returnValue($this->quote));
        $this->item->expects($this->any())->method('getAddress')->will($this->returnValue($this->address));

        $this->validator = $this->createMock(Validator::class);
        /** @var DataFactory|MockObject $dataFactory */
        $dataFactory = $this->createPartialMock(
            DataFactory::class,
            ['create']
        );
        $dataFactory->expects($this->any())->method('create')->will($this->returnValue($this->data));
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
        $this->rule->setData(['id' => 1, 'discount_amount' => 10.0]);

        $this->quote->expects($this->any())->method('getCartFixedRules')->will($this->returnValue([]));
        $store = $this->createMock(Store::class);
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

        $this->quote->expects($this->once())->method('setCartFixedRules')->with([1 => 0.0]);
        $this->model->calculate($this->rule, $this->item, 1);

        $this->assertEquals($this->data->getAmount(), 10);
        $this->assertEquals($this->data->getBaseAmount(), 10);
        $this->assertEquals($this->data->getOriginalAmount(), 10);
        $this->assertEquals($this->data->getBaseOriginalAmount(), 100);
    }
}
