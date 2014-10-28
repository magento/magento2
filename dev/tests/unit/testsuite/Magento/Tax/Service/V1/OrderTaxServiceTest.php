<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Service\V1;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTax;
use Magento\Tax\Service\V1\Data\OrderTaxDetails\Item;
use Magento\Tax\Service\V1\Data\OrderTaxDetails;

/**
 * Test TaxCalculationService
 */
class OrderTaxServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Tax\Service\V1\OrderTaxService */
    private $ordertTaxService;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order */
    private $order;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Model\Resource\Sales\Order\Tax\Item */
    private $orderItemTaxResource;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\OrderFactory */
    private $orderFactory;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->order = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['load', '__wakeup'])
            ->getMock();
        $this->orderFactory = $this->getMockBuilder('\Magento\Sales\Model\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->order));

        $this->orderItemTaxResource = $this->getMockBuilder('\Magento\Tax\Model\Resource\Sales\Order\Tax\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getTaxItemsByOrderId', '__wakeup'])
            ->getMock();

        $orderItemTaxFactory = $this->getMockBuilder('\Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create', '__wakeup'])
            ->getMock();
        $orderItemTaxFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->orderItemTaxResource));

        $this->ordertTaxService = $objectManager->getObject(
            'Magento\Tax\Service\V1\OrderTaxService',
            [
                'orderFactory' => $this->orderFactory,
                'orderItemTaxFactory' => $orderItemTaxFactory,
            ]
        );
    }

    /**
     * @param array $orderItemAppliedTaxes
     * @param array $expectedResults
     * @return void
     * @dataProvider getOrderTaxDetailsDataProvider
     */
    public function testGetOrderTaxDetails($orderItemAppliedTaxes, $expectedResults)
    {
        $orderId = 1;

        $this->order->expects($this->once())
            ->method('load')
            ->with($orderId)
            ->will($this->returnValue(true));

        $this->orderItemTaxResource->expects($this->once())
            ->method('getTaxItemsByOrderId')
            ->with($orderId)
            ->will($this->returnValue($orderItemAppliedTaxes));

        $orderTaxDetails = $this->ordertTaxService->getOrderTaxDetails($orderId);
        $this->assertEquals($expectedResults, $orderTaxDetails->__toArray());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOrderTaxDetailsDataProvider()
    {
        return [
            'two_products_with_shipping_weee' => [
                //Two items, 4 taxes
                'orderItemAppliedTaxes' => [
                    [
                        'item_id' => 53,
                        'taxable_item_type' => 'product',
                        'associated_item_id' => null,
                        'code' => 'US-CA-*-Rate 1',
                        'title' => 'US-CA-*-Rate 1',
                        'tax_percent' => '8.25',
                        'real_amount' => '6.1889',
                        'real_base_amount' => '12.3779',
                    ],
                    [
                        'item_id' => 54,
                        'taxable_item_type' => 'product',
                        'associated_item_id' => null,
                        'code' => 'US-CA-*-Rate 1',
                        'title' => 'US-CA-*-Rate 1',
                        'tax_percent' => '8.25',
                        'real_amount' => '12.3721',
                        'real_base_amount' => '24.7500',
                    ],
                    [
                        'item_id' => null,
                        'taxable_item_type' => 'weee',
                        'associated_item_id' => 54,
                        'code' => 'US-CA-*-Rate 1',
                        'title' => 'US-CA-*-Rate 1',
                        'tax_percent' => '8.25',
                        'real_amount' => '1.2389',
                        'real_base_amount' => '2.4721',
                    ],
                    [
                        'item_id' => 53,
                        'taxable_item_type' => 'product',
                        'associated_item_id' => null,
                        'code' => 'SanJose City Tax',
                        'title' => 'SanJose City Tax',
                        'tax_percent' => '6',
                        'real_amount' => '4.5011',
                        'real_base_amount' => '9.0021',
                    ],
                    [
                        'item_id' => 54,
                        'taxable_item_type' => 'product',
                        'associated_item_id' => null,
                        'code' => 'SanJose City Tax',
                        'title' => 'SanJose City Tax',
                        'tax_percent' => '6',
                        'real_amount' => '8.9979',
                        'real_base_amount' => '18',
                    ],
                    [
                        'item_id' => null,
                        'taxable_item_type' => 'weee',
                        'associated_item_id' => 54,
                        'code' => 'SanJose City Tax',
                        'title' => 'SanJose City Tax',
                        'tax_percent' => '6',
                        'real_amount' => '0.9011',
                        'real_base_amount' => '1.7979',
                    ],
                    [
                        'item_id' => 53,
                        'taxable_item_type' => 'product',
                        'associated_item_id' => null,
                        'code' => 'SST',
                        'title' => 'SST',
                        'tax_percent' => '5.7125',
                        'real_amount' => '4.28',
                        'real_base_amount' => '8.57',
                    ],
                    [
                        'item_id' => 54,
                        'taxable_item_type' => 'product',
                        'associated_item_id' => null,
                        'code' => 'SST',
                        'title' => 'SST',
                        'tax_percent' => '5.7125',
                        'real_amount' => '8.57',
                        'real_base_amount' => '17.14',
                    ],
                    [
                        'item_id' => null,
                        'taxable_item_type' => 'weee',
                        'associated_item_id' => 54,
                        'code' => 'SST',
                        'title' => 'SST',
                        'tax_percent' => '5.7125',
                        'real_amount' => '0.86',
                        'real_base_amount' => '1.71',
                    ],
                    [
                        'item_id' => null,
                        'taxable_item_type' => 'shipping',
                        'associated_item_id' => null,
                        'code' => 'Shipping',
                        'title' => 'Shipping',
                        'tax_percent' => '21',
                        'real_amount' => '2.6',
                        'real_base_amount' => '5.21',
                    ],
                ],
                'expectedResults' => [
                    OrderTaxDetails::KEY_ITEMS => [
                        [
                            Item::KEY_TYPE => 'product',
                            Item::KEY_ITEM_ID => 53,
                            Item::KEY_ASSOCIATED_ITEM_ID => null,
                            Item::KEY_APPLIED_TAXES => [
                                'US-CA-*-Rate 1' => [
                                    AppliedTax::KEY_CODE => 'US-CA-*-Rate 1',
                                    AppliedTax::KEY_TITLE => 'US-CA-*-Rate 1',
                                    AppliedTax::KEY_PERCENT => '8.25',
                                    AppliedTax::KEY_AMOUNT => '6.1889',
                                    AppliedTax::KEY_BASE_AMOUNT => '12.3779',
                                ],
                                'SanJose City Tax' => [
                                    AppliedTax::KEY_CODE => 'SanJose City Tax',
                                    AppliedTax::KEY_TITLE => 'SanJose City Tax',
                                    AppliedTax::KEY_PERCENT => '6',
                                    AppliedTax::KEY_AMOUNT => '4.5011',
                                    AppliedTax::KEY_BASE_AMOUNT => '9.0021',
                                ],
                                'SST' => [
                                    AppliedTax::KEY_CODE => 'SST',
                                    AppliedTax::KEY_TITLE => 'SST',
                                    AppliedTax::KEY_PERCENT => '5.7125',
                                    AppliedTax::KEY_AMOUNT => '4.28',
                                    AppliedTax::KEY_BASE_AMOUNT => '8.57',
                                ],
                            ]
                        ],
                        [
                            Item::KEY_TYPE => 'product',
                            Item::KEY_ITEM_ID => 54,
                            Item::KEY_ASSOCIATED_ITEM_ID => null,
                            Item::KEY_APPLIED_TAXES => [
                                'US-CA-*-Rate 1' => [
                                    AppliedTax::KEY_CODE => 'US-CA-*-Rate 1',
                                    AppliedTax::KEY_TITLE => 'US-CA-*-Rate 1',
                                    AppliedTax::KEY_PERCENT => '8.25',
                                    AppliedTax::KEY_AMOUNT => '12.3721',
                                    AppliedTax::KEY_BASE_AMOUNT => '24.7500',
                                ],
                                'SanJose City Tax' => [
                                    AppliedTax::KEY_CODE => 'SanJose City Tax',
                                    AppliedTax::KEY_TITLE => 'SanJose City Tax',
                                    AppliedTax::KEY_PERCENT => '6',
                                    AppliedTax::KEY_AMOUNT => '8.9979',
                                    AppliedTax::KEY_BASE_AMOUNT => '18',
                                ],
                                'SST' => [
                                    AppliedTax::KEY_CODE => 'SST',
                                    AppliedTax::KEY_TITLE => 'SST',
                                    AppliedTax::KEY_PERCENT => '5.7125',
                                    AppliedTax::KEY_AMOUNT => '8.57',
                                    AppliedTax::KEY_BASE_AMOUNT => '17.14',
                                ],
                            ]
                        ],
                        [
                            Item::KEY_TYPE => 'weee',
                            Item::KEY_ITEM_ID => null,
                            Item::KEY_ASSOCIATED_ITEM_ID => 54,
                            Item::KEY_APPLIED_TAXES => [
                                'US-CA-*-Rate 1' => [
                                    AppliedTax::KEY_CODE => 'US-CA-*-Rate 1',
                                    AppliedTax::KEY_TITLE => 'US-CA-*-Rate 1',
                                    AppliedTax::KEY_PERCENT => '8.25',
                                    AppliedTax::KEY_AMOUNT => '1.2389',
                                    AppliedTax::KEY_BASE_AMOUNT => '2.4721',
                                ],
                                'SanJose City Tax' => [
                                    AppliedTax::KEY_CODE => 'SanJose City Tax',
                                    AppliedTax::KEY_TITLE => 'SanJose City Tax',
                                    AppliedTax::KEY_PERCENT => '6',
                                    AppliedTax::KEY_AMOUNT => '0.9011',
                                    AppliedTax::KEY_BASE_AMOUNT => '1.7979',
                                ],
                                'SST' => [
                                    AppliedTax::KEY_CODE => 'SST',
                                    AppliedTax::KEY_TITLE => 'SST',
                                    AppliedTax::KEY_PERCENT => '5.7125',
                                    AppliedTax::KEY_AMOUNT => '0.86',
                                    AppliedTax::KEY_BASE_AMOUNT => '1.71',
                                ],
                            ]
                        ],
                        [
                            Item::KEY_TYPE => 'shipping',
                            Item::KEY_ITEM_ID => null,
                            Item::KEY_ASSOCIATED_ITEM_ID => null,
                            Item::KEY_APPLIED_TAXES => [
                                'Shipping' => [
                                    AppliedTax::KEY_CODE => 'Shipping',
                                    AppliedTax::KEY_TITLE => 'Shipping',
                                    AppliedTax::KEY_PERCENT => '21',
                                    AppliedTax::KEY_AMOUNT => '2.6',
                                    AppliedTax::KEY_BASE_AMOUNT => '5.21',
                                ],
                            ]
                        ],
                    ],
                    OrderTaxDetails::KEY_APPLIED_TAXES => [
                        [
                            AppliedTax::KEY_CODE => 'US-CA-*-Rate 1',
                            AppliedTax::KEY_TITLE => 'US-CA-*-Rate 1',
                            AppliedTax::KEY_PERCENT => '8.25',
                            AppliedTax::KEY_AMOUNT => '19.7999',
                            AppliedTax::KEY_BASE_AMOUNT => '39.6',
                        ],
                        [
                            AppliedTax::KEY_CODE => 'SanJose City Tax',
                            AppliedTax::KEY_TITLE => 'SanJose City Tax',
                            AppliedTax::KEY_PERCENT => '6',
                            AppliedTax::KEY_AMOUNT => '14.4001',
                            AppliedTax::KEY_BASE_AMOUNT => '28.8',
                        ],
                        [
                            AppliedTax::KEY_CODE => 'SST',
                            AppliedTax::KEY_TITLE => 'SST',
                            AppliedTax::KEY_PERCENT => '5.7125',
                            AppliedTax::KEY_AMOUNT => '13.71',
                            AppliedTax::KEY_BASE_AMOUNT => '27.42',
                        ],
                        [
                            AppliedTax::KEY_CODE => 'Shipping',
                            AppliedTax::KEY_TITLE => 'Shipping',
                            AppliedTax::KEY_PERCENT => '21',
                            AppliedTax::KEY_AMOUNT => '2.6',
                            AppliedTax::KEY_BASE_AMOUNT => '5.21',
                        ],
                    ],
                ],
            ],
        ];
    }
}
