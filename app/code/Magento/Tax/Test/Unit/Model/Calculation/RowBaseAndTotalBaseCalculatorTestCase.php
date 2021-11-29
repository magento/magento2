<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\AppliedTaxInterface;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Calculation\RowBaseCalculator;
use Magento\Tax\Model\Calculation\TotalBaseCalculator;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxDetails\AppliedTaxRate;
use Magento\Tax\Model\TaxDetails\ItemDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowBaseAndTotalBaseCalculatorTestCase extends TestCase
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

    /** @var MockObject */
    protected $taxItemDetailsDataObjectFactory;

    /** @var MockObject */
    protected $mockCalculationTool;

    /** @var MockObject */
    protected $mockConfig;

    /** @var QuoteDetailsItemInterface|MockObject */
    protected $mockItem;

    /** @var MockObject */
    protected $appliedTaxDataObjectFactory;

    /** @var MockObject */
    protected $appliedTaxRateDataObjectFactory;

    /** @var MockObject */
    protected $mockAppliedTax;

    /** @var DataObject */
    protected $addressRateRequest;

    /** @var  AppliedTaxRateInterface */
    protected $appliedTaxRate;

    /**
     * @var TaxDetailsItemInterface
     */
    protected $taxDetailsItem;

    /**
     * @var QuoteDetailsItemExtensionInterface|MockObject
     */
    private $quoteDetailsItemExtension;

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

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->taxItemDetailsDataObjectFactory = $this->createPartialMock(
            TaxDetailsItemInterfaceFactory::class,
            ['create']
        );
        $this->taxDetailsItem = $this->objectManager->getObject(ItemDetails::class);
        $this->taxItemDetailsDataObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->taxDetailsItem);

        $this->mockCalculationTool = $this->getMockBuilder(Calculation::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['__wakeup', 'round', 'getRate', 'getStoreRate', 'getRateRequest', 'getAppliedRates']
            )
            ->getMock();
        $this->mockConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockItem = $this->getMockBuilder(QuoteDetailsItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes', 'getUnitPrice'])
            ->getMockForAbstractClass();
        $this->quoteDetailsItemExtension = $this->getMockBuilder(QuoteDetailsItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceForTaxCalculation'])
            ->getMockForAbstractClass();
        $this->mockItem->expects($this->any())->method('getExtensionAttributes')
            ->willReturn($this->quoteDetailsItemExtension);

        $this->appliedTaxDataObjectFactory = $this->createPartialMock(
            AppliedTaxInterfaceFactory::class,
            ['create']
        );

        $this->appliedTaxRateDataObjectFactory = $this->createPartialMock(
            AppliedTaxRateInterfaceFactory::class,
            ['create']
        );
        $this->appliedTaxRate = $this->objectManager->getObject(AppliedTaxRate::class);
        $this->appliedTaxRateDataObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->appliedTaxRate);
        $this->mockAppliedTax = $this->getMockBuilder(AppliedTaxInterface::class)
            ->getMock();

        $this->mockAppliedTax->expects($this->any())->method('getTaxRateKey')->willReturn('taxKey');
        $this->addressRateRequest = new DataObject();
    }

    /**
     * @param $calculator RowBaseCalculator|TotalBaseCalculator
     * @param boolean $round
     * @return TaxDetailsItemInterface
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
                    return round((float) $price, 2);
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
     * @param MockObject $mockObject
     * @param array $mockMap
     */
    private function mockReturnValues($mockObject, $mockMap)
    {
        foreach ($mockMap as $valueMap) {
            if (isset($valueMap[self::WITH_ARGUMENT])) {
                $mockObject->expects(
                    $valueMap[self::ONCE] == true ? $this->once() : $this->atLeastOnce()
                )->method($valueMap[self::MOCK_METHOD_NAME])->with($valueMap[self::WITH_ARGUMENT])
                    ->willReturn(
                        $valueMap[self::MOCK_VALUE]
                    );
            } else {
                $mockObject->expects(
                    $valueMap[self::ONCE] == true ? $this->once() : $this->atLeastOnce()
                )->method($valueMap[self::MOCK_METHOD_NAME])->withAnyParameters()
                    ->willReturn(
                        $valueMap[self::MOCK_VALUE]
                    );
            }
        }
    }
}
