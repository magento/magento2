<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data;

class CartMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Service\V1\Data\CartMapper
     */
    protected $mapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerMapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsMapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyMapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $itemTotalsMapper;

    protected function setUp()
    {
        $this->totalsBuilder = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\TotalsBuilder',
            ['populateWithArray', 'setItems', 'create'],
            [],
            '',
            false
        );
        $this->cartBuilder = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\CartBuilder',
            [],
            [],
            '',
            false
        );
        $this->customerBuilder = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\CustomerBuilder',
            [],
            [],
            '',
            false
        );
        $this->customerMapper = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\CustomerMapper',
            [],
            [],
            '',
            false
        );
        $this->totalsMapper = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\TotalsMapper', [], [], '', false);
        $this->currencyMapper = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\CurrencyMapper',
            ['extractDto'],
            [],
            '',
            false
        );
        $this->itemTotalsMapper = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\Totals\ItemMapper',
            ['extractDto'],
            [],
            '',
            false
        );

        $this->mapper = new \Magento\Checkout\Service\V1\Data\CartMapper(
            $this->totalsBuilder,
            $this->cartBuilder,
            $this->customerBuilder,
            $this->customerMapper,
            $this->totalsMapper,
            $this->currencyMapper,
            $this->itemTotalsMapper
        );
    }

    public function testMap()
    {
        $methods = ['getId', 'getStoreId', 'getCreatedAt','getUpdatedAt', 'getConvertedAt', 'getIsActive',
            'getIsVirtual', 'getItemsCount', 'getItemsQty', 'getCheckoutMethod', 'getReservedOrderId', 'getOrigOrderId',
            'getAllItems', '__wakeUp', ];
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', $methods, [], '', false);
        $itemMock = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);
        $quoteMock->expects($this->once())->method('getAllItems')->will($this->returnValue([$itemMock]));
        $expected = [
            Cart::ID => 12,
            Cart::STORE_ID => 1,
            Cart::CREATED_AT => '2014-04-02 12:28:50',
            Cart::UPDATED_AT => '2014-04-02 12:28:50',
            Cart::CONVERTED_AT => '2014-04-02 12:28:50',
            Cart::IS_ACTIVE => true,
            Cart::IS_VIRTUAL => false,
            Cart::ITEMS_COUNT => 10,
            Cart::ITEMS_QUANTITY => 15,
            Cart::CHECKOUT_METHOD => 'check mo',
            Cart::RESERVED_ORDER_ID => 'order_id',
            Cart::ORIG_ORDER_ID => 'orig_order_id',
        ];
        $expectedMethods = [
            'getId' => 12,
            'getStoreId' => 1,
            'getCreatedAt' => '2014-04-02 12:28:50',
            'getUpdatedAt' => '2014-04-02 12:28:50',
            'getConvertedAt' => '2014-04-02 12:28:50',
            'getIsActive' => true,
            'getIsVirtual' => false,
            'getItemsCount' => 10,
            'getItemsQty' => 15,
            'getCheckoutMethod' => 'check mo',
            'getReservedOrderId' => 'order_id',
            'getOrigOrderId' => 'orig_order_id',
        ];
        foreach ($expectedMethods as $method => $value) {
            $quoteMock->expects($this->once())->method($method)->will($this->returnValue($value));
        }
        $this->customerMapper->expects($this->once())->method('map')->with($quoteMock)
            ->will($this->returnValue(['testCustomer']));
        $this->customerBuilder->expects($this->once())->method('populateWithArray')->with(['testCustomer']);
        $this->customerBuilder->expects($this->once())->method('create')->will($this->returnValue('customer'));

        $this->totalsMapper->expects($this->once())->method('map')->with($quoteMock)
            ->will($this->returnValue(['testTotals']));
        $this->totalsBuilder->expects($this->once())->method('populateWithArray')->with(['testTotals']);
        $this->totalsBuilder->expects($this->once())->method('create')->will($this->returnValue('totals'));

        $this->itemTotalsMapper->expects($this->once())->method('extractDto')->with($itemMock)
            ->will($this->returnValue('mappedItem'));

        $this->totalsBuilder->expects($this->once())->method('setItems')->with(['mappedItem']);

        $this->currencyMapper->expects($this->once())->method('extractDto')->with($quoteMock)
            ->will($this->returnValue('currency'));

        $this->cartBuilder->expects($this->once())->method('populateWithArray')->with($expected);
        $this->cartBuilder->expects($this->once())->method('setCustomer')->with('customer');
        $this->cartBuilder->expects($this->once())->method('setTotals')->with('totals');
        $this->cartBuilder->expects($this->once())->method('setCurrency')->with('currency');
        $this->mapper->map($quoteMock);
    }
}
