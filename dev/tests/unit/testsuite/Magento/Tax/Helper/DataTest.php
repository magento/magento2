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

namespace Magento\Tax\Helper;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTax;
use Magento\Tax\Service\V1\Data\OrderTaxDetails\Item;
use Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTaxBuilder;
use Magento\Tax\Service\V1\Data\OrderTaxDetails\ItemBuilder;
use Magento\Tax\Service\V1\Data\OrderTaxDetails;
use Magento\Tax\Service\V1\Data\OrderTaxDetailsBuilder;
/**
 * Test tax helper
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Tax\Helper\Data */
    private $taxHelper;

    /** @var  \Magento\Tax\Service\V1\Data\OrderTaxDetailsBuilder */
    private $orderTaxDetailsBuilder;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Service\V1\OrderTaxService */
    private $orderTaxService;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Registry */
    private $coreRegistry;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->coreRegistry = $this->getMockBuilder('\Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();

        $this->orderTaxService = $this->getMockBuilder('\Magento\Tax\Service\V1\OrderTaxService')
            ->disableOriginalConstructor()
            ->setMethods(['getOrderTaxDetails'])
            ->getMock();

        $this->taxHelper = $objectManager->getObject(
            'Magento\Tax\Helper\Data',
            [
                'coreRegistry' => $this->coreRegistry,
                'orderTaxService' => $this->orderTaxService,
            ]
        );

        $this->orderTaxDetailsBuilder = $objectManager->getObject('Magento\Tax\Service\V1\Data\OrderTaxDetailsBuilder');
    }

    /**
     * @param \Magento\Framework\Object $source
     * @param OrderTaxDetails $orderTaxDetails
     * @param array $expectedResults
     * @dataProvider getCalculatedTaxesOrderDataProvider
     */
    public function testGetCalculatedTaxesOrder($source, $orderTaxDetails, $expectedResults)
    {
        $this->orderTaxService->expects($this->any())
            ->method('getOrderTaxDetails')
            ->will($this->returnValue($orderTaxDetails));

        $orderTaxDetails = $this->taxHelper->getCalculatedTaxes($source);
        $this->assertEquals($expectedResults, $orderTaxDetails);
    }

    public function getCalculatedTaxesOrderDataProvider()
    {
        /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store */
        $store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['roundPrice', '__wakeup'])
            ->getMock();
        $store->expects($this->any())
            ->method('roundPrice')
            ->will($this->returnCallback(
                function ($argument) {
                    return round($argument, 2);
                }
            ));

        $objectManager = new ObjectManager($this);
        $this->orderTaxDetailsBuilder = $objectManager->getObject('Magento\Tax\Service\V1\Data\OrderTaxDetailsBuilder');
        $data = [
            '4_tax_rates_with_weee' => [
                'source' => new \Magento\Framework\Object(
                        [
                            'id' => '19',
                            'store' => $store,
                        ]
                    ),
                'orderTaxDetails' => $this->orderTaxDetailsBuilder->populateWithArray(
                        [
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
                        ]
                    )->create(),
                'expectedResults' => [
                    [
                        'tax_amount' => '19.80',
                        'base_tax_amount' => '39.6',
                        'title' => 'US-CA-*-Rate 1',
                        'percent' => '8.25',
                    ],
                    [
                        'tax_amount' => '14.40',
                        'base_tax_amount' => '28.8',
                        'title' => 'SanJose City Tax',
                        'percent' => '6',
                    ],
                    [
                        'tax_amount' => '13.71',
                        'base_tax_amount' => '27.42',
                        'title' => 'SST',
                        'percent' => '5.7125',
                    ],
                    [
                        'tax_amount' => '2.6',
                        'base_tax_amount' => '5.21',
                        'title' => 'Shipping',
                        'percent' => '21',
                    ]
                ],
            ],
            'empty_source' => [
                'source' => null,
                'orderTaxDetails' => $this->orderTaxDetailsBuilder->populateWithArray([])
                                        ->create(),
                'expectedResults' => [

                ],
            ]
        ];
        return $data;
    }

    protected function commonTestGetCalculatedTaxesInvoiceCreditmemo($source, $orderTaxDetails, $expectedResults)
    {
        $this->orderTaxService->expects($this->once())
            ->method('getOrderTaxDetails')
            ->with($source->getId())
            ->will($this->returnValue($orderTaxDetails));

        $orderTaxDetails = $this->taxHelper->getCalculatedTaxes($source);
        $this->assertEquals($expectedResults, $orderTaxDetails);
    }

    /**
     * @param \Magento\Framework\Object $source
     * @param \Magento\Framework\Object $invoice
     * @param OrderTaxDetails $orderTaxDetails
     * @param array $expectedResults
     * @dataProvider testGetCalculatedTaxesInvoiceCreditmemoDataProvider
     */
    public function testGetCalculatedTaxesInvoice($source, $invoice, $orderTaxDetails, $expectedResults)
    {
        $this->coreRegistry->expects($this->at(0))
            ->method('registry')
            ->with('current_invoice')
            ->will($this->returnValue($invoice));
        $this->coreRegistry->expects($this->at(1))
            ->method('registry')
            ->with('current_invoice')
            ->will($this->returnValue($invoice));
        $this->coreRegistry->expects($this->at(2))
            ->method('registry')
            ->with('current_invoice')
            ->will($this->returnValue($invoice));
        $this->coreRegistry->expects($this->at(3))
            ->method('registry')
            ->with('current_invoice')
            ->will($this->returnValue($invoice));
        $this->commonTestGetCalculatedTaxesInvoiceCreditmemo($source, $orderTaxDetails, $expectedResults);
    }

    /**
     * @param \Magento\Framework\Object $source
     * @param \Magento\Framework\Object $creditmemo
     * @param OrderTaxDetails $orderTaxDetails
     * @param array $expectedResults
     * @dataProvider testGetCalculatedTaxesInvoiceCreditmemoDataProvider
     */
    public function testGetCalculatedTaxesCreditmemo($source, $creditmemo, $orderTaxDetails, $expectedResults)
    {
        $this->coreRegistry->expects($this->at(0))
            ->method('registry')
            ->with('current_invoice')
            ->will($this->returnValue(null));
        $this->coreRegistry->expects($this->at(1))
            ->method('registry')
            ->with('current_creditmemo')
            ->will($this->returnValue($creditmemo));
        $this->coreRegistry->expects($this->at(2))
            ->method('registry')
            ->with('current_creditmemo')
            ->will($this->returnValue($creditmemo));
        $this->coreRegistry->expects($this->at(3))
            ->method('registry')
            ->with('current_invoice')
            ->will($this->returnValue(null));
        $this->coreRegistry->expects($this->at(4))
            ->method('registry')
            ->with('current_creditmemo')
            ->will($this->returnValue($creditmemo));
        $this->coreRegistry->expects($this->at(5))
            ->method('registry')
            ->with('current_creditmemo')
            ->will($this->returnValue($creditmemo));
        $this->commonTestGetCalculatedTaxesInvoiceCreditmemo($source, $orderTaxDetails, $expectedResults);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetCalculatedTaxesInvoiceCreditmemoDataProvider()
    {
        /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store */
        $store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['roundPrice', '__wakeup'])
            ->getMock();
        $store->expects($this->any())
            ->method('roundPrice')
            ->will($this->returnCallback(
                function ($argument) {
                    return round($argument, 2);
                }
            ));

        $objectManager = new ObjectManager($this);
        $this->orderTaxDetailsBuilder = $objectManager->getObject('Magento\Tax\Service\V1\Data\OrderTaxDetailsBuilder');
        $orderTaxDetails = $this->orderTaxDetailsBuilder->populateWithArray(
            [
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
            ]
        )->create();

        $data = [
            'qty_not_changed' => [
                'source' => new \Magento\Framework\Object(
                        [
                            'id' => '19',
                            'store' => $store,
                        ]
                    ),
                'current' => new \Magento\Framework\Object(
                        [
                            'shipping_tax_amount' => '2.6',
                            'base_shipping_tax_amount' => '5.21',
                            'items_collection' => [
                                '53' => new \Magento\Framework\Object(
                                        [
                                            'order_item' => new \Magento\Framework\Object(
                                                    [
                                                        'id' => 53,
                                                        'tax_amount' => 14.97,
                                                    ]
                                                ),
                                            'tax_amount' => 14.97,
                                        ]
                                    ),
                                '54' => new \Magento\Framework\Object(
                                        [
                                            'order_item' => new \Magento\Framework\Object(
                                                    [
                                                        'id' => 54,
                                                        'tax_amount' => 29.94,
                                                    ]
                                                ),
                                            'tax_amount' => 29.94,
                                        ]
                                    ),
                            ]
                        ]
                    ),
                'orderTaxDetails' => $orderTaxDetails,
                'expectedResults' => [
                    [
                        'tax_amount' => '2.6',
                        'base_tax_amount' => '5.21',
                        'title' => 'Shipping & Handling Tax',
                        'percent' => '',
                    ],
                    [
                        'title' => 'US-CA-*-Rate 1',
                        'percent' => '8.25',
                        'tax_amount' => '19.80',
                        'base_tax_amount' => '39.6',
                    ],
                    [
                        'title' => 'SanJose City Tax',
                        'percent' => '6',
                        'tax_amount' => '14.40',
                        'base_tax_amount' => '28.8',
                    ],
                    [
                        'title' => 'SST',
                        'percent' => '5.7125',
                        'tax_amount' => '13.71',
                        'base_tax_amount' => '27.42',
                    ],
                ],
            ],
        ];
        return $data;
    }
}
