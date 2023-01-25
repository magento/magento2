<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Model\Cart\SalesModel\Factory;
use Magento\Payment\Model\Cart\SalesModel\SalesModelInterface;
use Magento\Paypal\Model\Cart;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see \Magento\Paypal\Model\Cart
 */
class CartTest extends TestCase
{
    /**
     * @var Cart
     */
    protected $_model;

    /**
     * @var DataObject
     */
    protected $_validItem;

    /**
     * @var SalesModelInterface|MockObject
     */
    protected $_salesModel;

    /**
     * @param null|string $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_validItem = new DataObject(
            [
                'parent_item' => null,
                'price' => 2.0,
                'qty' => 3,
                'name' => 'valid item',
                'original_item' => new DataObject(['base_row_total' => 6.0]),
            ]
        );
    }

    protected function setUp(): void
    {
        $this->_salesModel = $this->getMockForAbstractClass(
            SalesModelInterface::class
        );
        $factoryMock = $this->createPartialMock(Factory::class, ['create']);
        $factoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'sales model'
        )->willReturn(
            $this->_salesModel
        );
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->_model = new Cart($factoryMock, $eventManagerMock, 'sales model');
    }

    /**
     * @param array $items
     * @dataProvider invalidGetAllItemsDataProvider
     */
    public function testInvalidGetAllItems($items)
    {
        $taxContainer = new DataObject(
            ['base_discount_tax_compensation_amount' => 0.2, 'base_shipping_discount_tax_compensation_amnt' => 0.1]
        );
        $this->_salesModel->expects($this->once())->method('getTaxContainer')->willReturn($taxContainer);
        $this->_salesModel->expects($this->once())->method('getAllItems')->willReturn($items);
        $this->_salesModel->expects($this->once())->method('getBaseSubtotal')->willReturn(2.1);
        $this->_salesModel->expects($this->once())->method('getBaseTaxAmount')->willReturn(0.1);
        $this->_salesModel->expects($this->once())->method('getBaseShippingAmount')->willReturn(1.1);
        $this->_salesModel->expects($this->once())->method('getBaseDiscountAmount')->willReturn(0.3);
        $this->assertEmpty($this->_model->getAllItems());
        $this->assertEquals(2.1, $this->_model->getSubtotal());
        $this->assertEquals(0.1 + 0.2 + 0.1, $this->_model->getTax());
        $this->assertEquals(1.1, $this->_model->getShipping());
        $this->assertEquals(0.3, $this->_model->getDiscount());
    }

    /**
     * @return array
     */
    public function invalidGetAllItemsDataProvider()
    {
        return [
            [[]],
            [
                [
                    new DataObject(
                        [
                            'parent_item' => new DataObject(),
                            'price' => 2.0,
                            'qty' => 3,
                            'name' => 'item 1',
                        ]
                    ),
                ]
            ],
            [
                [
                    $this->_validItem,
                    new DataObject(
                        [
                            'price' => 2.0,
                            'qty' => 3,
                            'name' => 'item 2',
                            'original_item' => new DataObject(['base_row_total' => 6.01]),
                        ]
                    ),
                ]
            ],
            [
                [
                    $this->_validItem,
                    new DataObject(
                        [
                            'price' => sqrt(2),
                            'qty' => sqrt(2),
                            'name' => 'item 3',
                            'original_item' => new DataObject(['base_row_total' => 2]),
                        ]
                    ),
                ]
            ]
        ];
    }

    /**
     * @param array $values
     * @param bool $transferDiscount
     * @dataProvider invalidTotalsGetAllItemsDataProvider
     */
    public function testInvalidTotalsGetAllItems($values, $transferDiscount)
    {
        $expectedSubtotal = $this->_prepareInvalidModelData($values, $transferDiscount);
        $baseShippingDiscountTaxCompensationAmount = $values['base_shipping_discount_tax_compensation_amount'] ??
            $values['base_shipping_discount_tax_compensation_amnt'];
        $this->assertEmpty($this->_model->getAllItems());
        $this->assertEquals($expectedSubtotal, $this->_model->getSubtotal());
        $this->assertEquals(
            $values['base_tax_amount'] +
            $values['base_discount_tax_compensation_amount'] +
            $baseShippingDiscountTaxCompensationAmount,
            $this->_model->getTax()
        );
        $this->assertEquals($values['base_shipping_amount'], $this->_model->getShipping());
        $this->assertEquals(
            $transferDiscount ? 0.0 : $values['base_discount_amount'],
            $this->_model->getDiscount()
        );
    }

    /**
     * @return array
     */
    public function invalidTotalsGetAllItemsDataProvider()
    {
        return [
            [
                [
                    'base_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amnt' => 0,
                    'base_shipping_discount_tax_compensation_amount' => null,
                    'base_subtotal' => 0,
                    'base_tax_amount' => 0,
                    'base_shipping_amount' => 0,
                    'base_discount_amount' => 6.1,
                    'base_grand_total' => 0,
                ],
                false,
            ],
            [
                [
                    'base_discount_tax_compensation_amount' => 1,
                    'base_shipping_discount_tax_compensation_amount' => 2,
                    'base_shipping_discount_tax_compensation_amnt' => null,
                    'base_subtotal' => 3,
                    'base_tax_amount' => 4,
                    'base_shipping_amount' => 5,
                    'base_discount_amount' => 100,
                    'base_grand_total' => 5.5,
                ],
                true
            ]
        ];
    }

    public function testGetAllItems()
    {
        $totals = $this->_prepareValidModelData();
        $this->assertEquals(
            [
                new DataObject(
                    [
                        'name' => $this->_validItem->getName(),
                        'qty' => $this->_validItem->getQty(),
                        'amount' => $this->_validItem->getPrice(),
                    ]
                ),
            ],
            $this->_model->getAllItems()
        );
        $this->assertEquals($totals['subtotal'], $this->_model->getSubtotal());
        $this->assertEquals($totals['tax'], $this->_model->getTax());
        $this->assertEquals($totals['shipping'], $this->_model->getShipping());
        $this->assertEquals($totals['discount'], $this->_model->getDiscount());
    }

    /**
     * @param array $values
     * @param bool $transferDiscount
     * @param bool $transferShipping
     * @dataProvider invalidGetAmountsDataProvider
     */
    public function testInvalidGetAmounts($values, $transferDiscount, $transferShipping)
    {
        $expectedSubtotal = $this->_prepareInvalidModelData($values, $transferDiscount);
        if ($transferShipping) {
            $this->_model->setTransferShippingAsItem();
        }
        $result = $this->_model->getAmounts();
        $expectedSubtotal += $this->_model->getTax();
        $expectedSubtotal += $values['base_shipping_amount'];
        if (!$transferDiscount) {
            $expectedSubtotal -= $this->_model->getDiscount();
        }
        $this->assertEquals([Cart::AMOUNT_SUBTOTAL => $expectedSubtotal], $result);
    }

    /**
     * @return array
     */
    public function invalidGetAmountsDataProvider()
    {
        $data = [];
        $invalidTotalsData = $this->invalidTotalsGetAllItemsDataProvider();
        foreach ($invalidTotalsData as $dataItem) {
            $data[] = [$dataItem[0], $dataItem[1], true];
            $data[] = [$dataItem[0], $dataItem[1], false];
        }
        return $data;
    }

    /**
     * Prepare invalid data for cart
     *
     * @param array $data
     * @param bool $transferDiscount
     * @return float
     */
    protected function _prepareInvalidModelData($data, $transferDiscount)
    {
        $taxContainer = new DataObject(
            [
                'base_discount_tax_compensation_amount' =>
                    $data['base_discount_tax_compensation_amount'],
                'base_shipping_discount_tax_compensation_amnt' =>
                    $data['base_shipping_discount_tax_compensation_amnt'],
                'base_shipping_discount_tax_compensation_amount' =>
                    $data['base_shipping_discount_tax_compensation_amount']
            ]
        );
        $expectedSubtotal = $data['base_subtotal'];
        if ($transferDiscount) {
            $this->_model->setTransferDiscountAsItem();
            $expectedSubtotal -= $data['base_discount_amount'];
        }
        $this->_salesModel->expects($this->once())->method('getTaxContainer')->willReturn($taxContainer);
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getAllItems'
        )->willReturn(
            [$this->_validItem]
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseSubtotal'
        )->willReturn(
            $data['base_subtotal']
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseTaxAmount'
        )->willReturn(
            $data['base_tax_amount']
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseShippingAmount'
        )->willReturn(
            $data['base_shipping_amount']
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseDiscountAmount'
        )->willReturn(
            $data['base_discount_amount']
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getDataUsingMethod'
        )->with(
            'base_grand_total'
        )->willReturn(
            $data['base_grand_total']
        );
        return $expectedSubtotal;
    }

    public function testGetAmounts()
    {
        $totals = $this->_prepareValidModelData();
        $this->assertEquals($totals, $this->_model->getAmounts());
    }

    /**
     * Prepare valid cart data
     *
     * @return array
     */
    protected function _prepareValidModelData()
    {
        $totals = ['discount' => 0.1, 'shipping' => 0.2, 'subtotal' => 0.3, 'tax' => 0.4];
        $taxContainer = new DataObject(
            ['base_discount_tax_compensation_amount' => 0, 'base_shipping_discount_tax_compensation_amnt' => 0]
        );
        $this->_salesModel->expects($this->once())->method('getTaxContainer')->willReturn($taxContainer);
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getAllItems'
        )->willReturn(
            [$this->_validItem]
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseSubtotal'
        )->willReturn(
            $totals['subtotal']
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseTaxAmount'
        )->willReturn(
            $totals['tax']
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseShippingAmount'
        )->willReturn(
            $totals['shipping']
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseDiscountAmount'
        )->willReturn(
            $totals['discount']
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getDataUsingMethod'
        )->with(
            'base_grand_total'
        )->willReturn(
            6.0 + $totals['tax'] + $totals['shipping'] - $totals['discount']
        );
        return $totals;
    }

    public function testHasNegativeItemAmount()
    {
        $this->_model->addCustomItem('item1', 1, 2.1);
        $this->assertFalse($this->_model->hasNegativeItemAmount());
        $this->_model->addCustomItem('item1', 1, -1.3);
        $this->assertTrue($this->_model->hasNegativeItemAmount());
    }
}
