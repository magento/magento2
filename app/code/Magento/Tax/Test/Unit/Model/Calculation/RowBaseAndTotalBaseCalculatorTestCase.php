<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Tax\Model\Calculation\RowbaseCalculator;
use Magento\Tax\Model\Calculation\TotalBaseCalculator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RowBaseAndTotalBaseCalculatorTestCase extends \PHPUnit_Framework_TestCase
{
    const STORE_ID = 2300;
    const QUANTITY = 1;
    const UNIT_PRICE = 500;
    const RATE = 10;
    const STORE_RATE = 11;

    const UNIT_PRICE_INCL_TAX = 495.49549549545;
    const UNIT_PRICE_INCL_TAX_ROUNDED = 495.5;

    const CODE = 'CODE';
    const TYPE = 'TYPE';

    const ONCE = 'once';
    const MOCK_METHOD_NAME = 'mock_method_name';
    const MOCK_VALUE = 'mock_value';
    const WITH_ARGUMENT = 'with_argument';

    /** @var ObjectManager */
    protected $objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $taxItemDetailsDataObjectFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockCalculationTool;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockConfig;

    /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockItem;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $appliedTaxDataObjectFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $appliedTaxRateDataObjectFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockAppliedTax;

    protected $addressRateRequest;

    /** @var  \Magento\Tax\Api\Data\AppliedTaxRateInterface */
    protected $appliedTaxRate;

    /**
     * @var \Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected $taxDetailsItem;

    /**
     * initialize all mocks
     *
     * @param bool $isTaxIncluded
     */
    public function initMocks($isTaxIncluded)
    {
        $this->initMockItem($isTaxIncluded);
        $this->initMockConfig();
        $this->initMockCalculationTool($isTaxIncluded);
        $this->initMockAppliedTaxDataObjectFactory();
    }

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->taxItemDetailsDataObjectFactory = $this->getMock(
            'Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->taxDetailsItem = $this->objectManager->getObject('Magento\Tax\Model\TaxDetails\ItemDetails');
        $this->taxItemDetailsDataObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->taxDetailsItem);

        $this->mockCalculationTool = $this->getMockBuilder('\Magento\Tax\Model\Calculation')
            ->disableOriginalConstructor()
            ->setMethods(
                ['__wakeup', 'round', 'getRate', 'getStoreRate', 'getRateRequest', 'getAppliedRates']
            )
            ->getMock();
        $this->mockConfig = $this->getMockBuilder('\Magento\Tax\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockItem = $this->getMockBuilder('Magento\Tax\Api\Data\QuoteDetailsItemInterface')->getMock();

        $this->appliedTaxDataObjectFactory = $this->getMock(
            'Magento\Tax\Api\Data\AppliedTaxInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->appliedTaxRateDataObjectFactory = $this->getMock(
            'Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->appliedTaxRate = $this->objectManager->getObject('Magento\Tax\Model\TaxDetails\AppliedTaxRate');
        $this->appliedTaxRateDataObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->appliedTaxRate);
        $this->mockAppliedTax = $this->getMockBuilder('Magento\Tax\Api\Data\AppliedTaxInterface')->getMock();

        $this->mockAppliedTax->expects($this->any())->method('getTaxRateKey')->will($this->returnValue('taxKey'));
        $this->addressRateRequest = new \Magento\Framework\DataObject();
    }

    /**
     * @param $calculator RowBaseCalculator|TotalBaseCalculator
     * @param boolean $round
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    public function calculate($calculator, $round = true)
    {
        return $calculator->calculate($this->mockItem, 1, $round);
    }

    /**
     * init mock Items
     *
     * @param bool $isTaxIncluded
     */
    protected function initMockItem($isTaxIncluded)
    {
        $this->mockReturnValues(
            $this->mockItem,
            [
                [
                    self::ONCE => false,
                    self::MOCK_METHOD_NAME => 'getDiscountAmount',
                    self::MOCK_VALUE => 1,
                ],
                [
                    self::ONCE => false,
                    self::MOCK_METHOD_NAME => 'getCode',
                    self::MOCK_VALUE => self::CODE
                ],
                [
                    self::ONCE => false,
                    self::MOCK_METHOD_NAME => 'getType',
                    self::MOCK_VALUE => self::TYPE
                ],
                [
                    self::ONCE => false,
                    self::MOCK_METHOD_NAME => 'getUnitPrice',
                    self::MOCK_VALUE => self::UNIT_PRICE
                ],
                [
                    self::ONCE => false,
                    self::MOCK_METHOD_NAME => 'getIsTaxIncluded',
                    self::MOCK_VALUE => $isTaxIncluded
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
                    self::ONCE => false,
                    self::MOCK_METHOD_NAME => 'applyTaxAfterDiscount',
                    self::MOCK_VALUE => true,
                ]
            ]
        );
    }

    /**
     * init mock calculation model
     *
     * @param boolean $isTaxIncluded
     */

    protected function initMockCalculationTool($isTaxIncluded)
    {
        $mockValues = [
            [
                self::ONCE => false,
                self::MOCK_METHOD_NAME => 'getRate',
                self::MOCK_VALUE => self::RATE
            ],
            [
                self::ONCE => false,
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
        ];

        if ($isTaxIncluded) {
            $mockValues[] = [
                self::ONCE => false,
                self::MOCK_METHOD_NAME => 'getStoreRate',
                self::MOCK_VALUE => self::STORE_RATE
            ];
        }

        $this->mockReturnValues(
            $this->mockCalculationTool,
            $mockValues
        );
        $this->mockCalculationTool->expects($this->atLeastOnce())
            ->method('round')
            ->willReturnCallback(
                function ($price) {
                    return round($price, 2);
                }
            );
    }

    /**
     * init mock appliedTaxDataObjectFactory
     *
     */
    protected function initMockAppliedTaxDataObjectFactory()
    {
        $this->mockReturnValues(
            $this->appliedTaxDataObjectFactory,
            [
                [
                    self::ONCE => false,
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
