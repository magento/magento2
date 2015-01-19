<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\TestFramework\Helper\ObjectManager;

class RowBaseAndTotalBaseCalculatorTestCase extends \PHPUnit_Framework_TestCase
{
    const STORE_ID = 2300;
    const QUANTITY = 1;
    const UNIT_PRICE = 500;
    const RATE = 10;
    const STORE_RATE = 11;

    const CODE = 'CODE';
    const TYPE = 'TYPE';

    const ONCE = 'once';
    const MOCK_METHOD_NAME = 'mock_method_name';
    const MOCK_VALUE = 'mock_value';
    const WITH_ARGUMENT = 'with_argument';
    const EXPECTED_VALUE = "some_return_object";

    /** @var ObjectManager */
    protected $objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $taxItemDetailsBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockCalculationTool;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockItem;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $appliedTaxBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $appliedTaxRateBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockAppliedTax;

    protected $addressRateRequest;

    /**
     * initialize all mocks
     *
     * @param bool $taxIncluded
     */
    public function initMocks($taxIncluded)
    {
        $this->initMockItem($taxIncluded);
        $this->initMockConfig();
        $this->initMockCalculationTool();
        $this->initMockItemBuilder();
        $this->initMockAppliedTaxBuilder();
    }

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->taxItemDetailsBuilder = $this->getMock(
            'Magento\Tax\Api\Data\TaxDetailsItemDataBuilder',
            [
                'setCode', 'setType', 'setRowTax', 'setPrice', 'setPriceInclTax', 'setRowTotal', 'setRowTotalInclTax',
                'setDiscountTaxCompensationAmount', 'setAssociatedItemCode', 'setTaxPercent', 'setAppliedTaxes',
                'create'
            ],
            [],
            '',
            false
        );

        $this->mockCalculationTool = $this->getMockBuilder('\Magento\Tax\Model\Calculation')
            ->disableOriginalConstructor()
            ->setMethods(
                ['__wakeup', 'round', 'getRate', 'getStoreRate', 'getRateRequest', 'getAppliedRates', 'calcTaxAmount']
            )
            ->getMock();
        $this->mockConfig = $this->getMockBuilder('\Magento\Tax\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockItem = $this->getMockBuilder('Magento\Tax\Api\Data\QuoteDetailsItemInterface')->getMock();

        $this->appliedTaxBuilder = $this->getMock(
            'Magento\Tax\Api\Data\AppliedTaxDataBuilder',
            ['setAmount', 'setPercent', 'setTaxRateKey', 'setRates', 'create'],
            [],
            '',
            false
        );

        $this->appliedTaxRateBuilder = $this->getMock(
            'Magento\Tax\Api\Data\AppliedTaxRateDataBuilder',
            ['setPercent', 'setCode', 'setTitle', 'create'],
            [],
            '',
            false
        );
        $this->mockAppliedTax = $this->getMockBuilder('Magento\Tax\Api\Data\AppliedTaxInterface')->getMock();

        $this->mockAppliedTax->expects($this->any())->method('getTaxRateKey')->will($this->returnValue('taxKey'));
        //Magento\Tax\Service\V1\Data\TaxDetails
        $this->addressRateRequest = new \Magento\Framework\Object();
    }

    /**
     * @param $calculator RowBaseCalculator|TotalBaseCalculator
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    public function calculate($calculator)
    {
        return $calculator->calculate($this->mockItem, 1);
    }

    /**
     * init mock Items
     *
     * @param bool $taxIncluded
     */
    protected function initMockItem($taxIncluded)
    {
        $this->mockReturnValues(
            $this->mockItem,
            [
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'getDiscountAmount',
                    self::MOCK_VALUE => 1,
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'getCode',
                    self::MOCK_VALUE => self::CODE
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'getType',
                    self::MOCK_VALUE => self::TYPE
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'getUnitPrice',
                    self::MOCK_VALUE => self::UNIT_PRICE
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'getTaxIncluded',
                    self::MOCK_VALUE => $taxIncluded
                ]
            ]
        );
    }

    /**
     * init mock config
     *
     */
    protected function initMockConfig()
    {
        $this->mockReturnValues(
            $this->mockConfig,
            [
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'applyTaxAfterDiscount',
                    self::MOCK_VALUE => true,
                ]
            ]
        );
    }

    /**
     * init mock calculation model
     *
     */

    protected function initMockCalculationTool()
    {
        $this->mockReturnValues(
            $this->mockCalculationTool,
            [
                [
                    self::ONCE => false,
                    self::MOCK_METHOD_NAME => 'calcTaxAmount',
                    self::MOCK_VALUE => 1.5,
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'getRate',
                    self::MOCK_VALUE => self::RATE
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'getAppliedRates',
                    self::MOCK_VALUE => [
                        [
                            'id' => 0,
                            'percent' => 1.4,
                            'rates' => [
                                [
                                    'code' => 'sku_1',
                                    'title' => 'title1',
                                    'percent' => 1.1,
                                ],
                            ],
                        ],
                    ]
                ],
                [
                    self::ONCE => false,
                    self::MOCK_METHOD_NAME => 'round',
                    self::MOCK_VALUE => 1.3
                ]
            ]
        );
    }

    /**
     * init mock applied itemBuilder
     *
     */

    protected function initMockItemBuilder()
    {
        $this->mockReturnValues(
            $this->taxItemDetailsBuilder,
            [
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'setType',
                    self::MOCK_VALUE => self::TYPE,
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'setCode',
                    self::MOCK_VALUE => self::CODE
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'setRowTax',
                    self::MOCK_VALUE => 1.3
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'setTaxPercent',
                    self::MOCK_VALUE => self::RATE
                ],
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'create',
                    self::MOCK_VALUE => self::EXPECTED_VALUE
                ]
            ]

        );
    }

    /**
     * init mock appliedTaxBuilder
     *
     */
    protected function initMockAppliedTaxBuilder()
    {
        $this->mockReturnValues(
            $this->appliedTaxBuilder,
            [
                [
                    self::ONCE => true,
                    self::MOCK_METHOD_NAME => 'create',
                    self::MOCK_VALUE => $this->mockAppliedTax,
                ]
            ]
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mockObject
     * @param array $mockMap
     */
    private function mockReturnValues($mockObject, $mockMap)
    {
        foreach ($mockMap as $valueMap) {
            if (isset($valueMap[self::WITH_ARGUMENT])) {
                $mockObject->expects(
                    $valueMap[self::ONCE] == true ? $this->once() : $this->atLeastOnce()
                )->method($valueMap[self::MOCK_METHOD_NAME])->with($valueMap[self::WITH_ARGUMENT])
                    ->will(
                        $this->returnValue($valueMap[self::MOCK_VALUE])
                    );
            } else {
                $mockObject->expects(
                    $valueMap[self::ONCE] == true ? $this->once() : $this->atLeastOnce()
                )->method($valueMap[self::MOCK_METHOD_NAME])->withAnyParameters()
                    ->will(
                        $this->returnValue($valueMap[self::MOCK_VALUE])
                    );
            }
        }
    }
}
