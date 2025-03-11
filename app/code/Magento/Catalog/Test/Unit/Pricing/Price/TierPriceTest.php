<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Group\RetrieverInterface;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Pricing\Price\TierPrice
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceTest extends TestCase
{
    /**
     * Test customer group
     *
     * @var int
     */
    private static $customerGroup = Group::NOT_LOGGED_IN_ID;

    /**
     * @var MockObject
     */
    private $priceInfo;

    /**
     * @var MockObject
     */
    private $product;

    /**
     * @var float
     */
    private $quantity = 3.;

    /**
     * @var MockObject
     */
    private $calculator;

    /**
     * @var MockObject
     */
    private $session;

    /**
     * @var TierPrice
     */
    private $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var MockObject
     */
    private $groupManagement;

    /**
     * @var RetrieverInterface|MockObject
     */
    private $customerGroupRetriever;

    /**
     * @var MockObject
     */
    private $scopeConfigMock;

    /**
     * Initialize base dependencies
     */
    protected function setUp(): void
    {
        $this->priceInfo = $this->createMock(Base::class);

        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods(['hasCustomerGroupId', 'getCustomerGroupId'])
            ->onlyMethods(['getPriceInfo', 'getResource'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->any())->method('getPriceInfo')->willReturn($this->priceInfo);
        $this->customerGroupRetriever = $this->getMockBuilder(RetrieverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->session = $this->createMock(Session::class);
        $this->session->expects($this->any())->method('getCustomerGroupId')
            ->willReturn(self::$customerGroup);
        $this->customerGroupRetriever = $this->getMockForAbstractClass(RetrieverInterface::class);
        $this->calculator = $this->createMock(Calculator::class);
        $this->groupManagement = $this->getMockForAbstractClass(GroupManagementInterface::class);

        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->model = new TierPrice(
            $this->product,
            $this->quantity,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->session,
            $this->groupManagement,
            $this->customerGroupRetriever,
            $this->scopeConfigMock
        );
    }

    /**
     * Test base initialization of tier price
     *
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::__construct
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::getValue
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::getStoredTierPrices
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::canApplyTierPrice
     * @dataProvider providerForBaseInitialization
     */
    public function testBaseInitialization($tierPrices, $expectedValue)
    {
        $convertedExpectedValue = $expectedValue - 1;
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->willReturnCallback(
                function ($arg) {
                    return $arg -1;
                }
            );
        $this->product->setData(TierPrice::PRICE_CODE, $tierPrices);
        $group = $this->createMock(\Magento\Customer\Model\Data\Group::class);
        $group->expects($this->any())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagement->expects($this->any())->method('getAllCustomersGroup')->willReturn($group);
        $this->assertEquals($convertedExpectedValue, $this->model->getValue());
    }

    /**
     * @return array
     */
    public static function providerForBaseInitialization()
    {
        return [
            'case for getValue' => [
                'tierPrices' => [
                    [
                        'website_price' => '20.',
                        'price' => '20.',
                        'price_qty' => '1.',
                        'cust_group' => Group::CUST_GROUP_ALL,
                    ],
                    [
                        'website_price' => '10.',
                        'price' => '10.',
                        'price_qty' => '1.',
                        'cust_group' => Group::CUST_GROUP_ALL
                    ],
                ],
                'expectedValue' => 10.,
            ],
            'case for canApplyTierPrice' => [
                'tierPrices' => [
                    // tier not for current customer group
                    [
                        'website_price' => '10.',
                        'price' => '10.',
                        'price_qty' => '1.',
                        'cust_group' => self::$customerGroup + 1
                    ],
                    // tier is higher than product qty
                    [
                        'website_price' => '10.',
                        'price' => '10.',
                        'price_qty' => '10.',
                        'cust_group' => Group::CUST_GROUP_ALL
                    ],
                    // higher tier qty already found
                    [
                        'website_price' => '10.',
                        'price' => '10.',
                        'price_qty' => '0.5',
                        'cust_group' => Group::CUST_GROUP_ALL
                    ],
                    // found tier qty is same as current tier qty but current tier group is ALL_GROUPS
                    [
                        'website_price' => '5.',
                        'price' => '10.',
                        'price_qty' => '1.',
                        'cust_group' => self::$customerGroup
                    ],
                    [
                        'website_price' => '1.',
                        'price' => '10.',
                        'price_qty' => '1.',
                        'cust_group' => Group::CUST_GROUP_ALL
                    ],
                ],
                'expectedValue' => 5.,
            ],
        ];
    }

    /**
     * Test getter stored tier prices from eav model
     *
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::__construct
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::getStoredTierPrices
     */
    public function testGetterStoredTierPrices()
    {
        $this->product->expects($this->once())->method('hasCustomerGroupId')
            ->willReturn(true);
        $this->product->expects($this->once())->method('getCustomerGroupId')
            ->willReturn(self::$customerGroup);

        $backendMock = $this->createMock(AbstractBackend::class);

        $attributeMock = $this->createMock(AbstractAttribute::class);
        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);

        $productResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productResource->expects($this->once())->method('getAttribute')->with(TierPrice::PRICE_CODE)
            ->willReturn($attributeMock);

        $this->product->expects($this->once())->method('getResource')->willReturn($productResource);

        $tierPrice = new TierPrice(
            $this->product,
            $this->quantity,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->session,
            $this->groupManagement,
            $this->customerGroupRetriever,
            $this->scopeConfigMock
        );
        $group = $this->createMock(\Magento\Customer\Model\Data\Group::class);
        $group->expects($this->once())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagement->expects($this->once())->method('getAllCustomersGroup')
            ->willReturn($group);

        $this->assertFalse($tierPrice->getValue());
    }

    /**
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::__construct
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::getTierPriceList
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::getStoredTierPrices
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::applyAdjustment
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::getTierPriceCount
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::filterTierPrices
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::getBasePrice
     * @dataProvider providerForGetterTierPriceList
     */
    public function testGetterTierPriceList($tierPrices, $basePrice, $expectedResult)
    {
        $this->product->setData(TierPrice::PRICE_CODE, $tierPrices);

        $price = $this->getMockForAbstractClass(PriceInterface::class);
        $price->expects($this->any())->method('getValue')->willReturn($basePrice);

        $this->calculator->expects($this->atLeastOnce())->method('getAmount')
            ->willReturnArgument(0);

        $this->priceInfo->expects(static::atLeastOnce())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($price);
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->willReturnCallback(
                function ($arg) {
                    return round(0.5 * $arg, 2);
                }
            );

        $group = $this->createMock(\Magento\Customer\Model\Data\Group::class);
        $group->expects($this->any())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagement->expects($this->any())->method('getAllCustomersGroup')
            ->willReturn($group);
        $this->assertEquals($expectedResult, $this->model->getTierPriceList());
        $this->assertCount($this->model->getTierPriceCount(), $expectedResult);
        //Second call will get the cached value
        $this->assertEquals($expectedResult, $this->model->getTierPriceList());
        $this->assertCount($this->model->getTierPriceCount(), $expectedResult);
    }

    /**
     * @return array
     */
    public static function providerForGetterTierPriceList()
    {
        return [
            'base case' => [
                'tierPrices' => [
                    // will be ignored due to customer group
                    [
                        'price'         => '21.3',
                        'website_price' => '21.3',
                        'price_qty'     => '1.3',
                        'cust_group'    => self::$customerGroup + 1
                    ],
                    [
                        'price'         => '20.4',
                        'website_price' => '20.4',
                        'price_qty'     => '5.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                    // cases to calculate save percent
                    [
                        'price'         => '20.1',
                        'website_price' => '20.1',
                        'price_qty'     => '5.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                    [
                        'price'         => '30.2',
                        'website_price' => '30.2',
                        'price_qty'     => '5.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                    [
                        'price'         => '8.3',
                        'website_price' => '8.3',
                        'price_qty'     => '2.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                ],
                'basePrice' => 20.,
                'expectedResult' => [
                    [
                        'price'          => '10.05',
                        'website_price'  => '10.05',
                        'price_qty'      => '5.',
                        'cust_group'     => Group::CUST_GROUP_ALL,
                    ],
                    [
                        'price'         => '4.15',
                        'website_price' => '4.15',
                        'price_qty'     => '2.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                ],
            ]
        ];
    }

    /**
     * @param float $basePrice
     * @param float $tierPrice
     * @param float $savedPercent
     *
     * @dataProvider dataProviderGetSavePercent
     */
    public function testGetSavePercent($basePrice, $tierPrice, $savedPercent)
    {
        /** @var AmountInterface|MockObject $amount */
        $amount = $this->getMockForAbstractClass(AmountInterface::class);

        $amount->expects($this->any())
            ->method('getValue')
            ->willReturn($tierPrice);

        $basePriceAmount = $this->getMockForAbstractClass(AmountInterface::class);

        $basePriceAmount->expects($this->any())
            ->method('getValue')
            ->willReturn($basePrice);

        $price = $this->getMockForAbstractClass(PriceInterface::class);

        $price->expects($this->any())
            ->method('getAmount')
            ->willReturn($basePriceAmount);

        $this->priceInfo->expects($this->any())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($price);

        $this->assertEquals($savedPercent, $this->model->getSavePercent($amount));
    }

    /**
     * @return array
     */
    public static function dataProviderGetSavePercent()
    {
        return [
            ['basePrice' => '100', 'tierPrice' => '90', 'savedPercent' => '10'],
            ['basePrice' => '70', 'tierPrice' => '35', 'savedPercent' => '50'],
            ['basePrice' => '50', 'tierPrice' => '35', 'savedPercent' => '30'],
            ['basePrice' => '20.80', 'tierPrice' => '18.72', 'savedPercent' => '10']
        ];
    }

    /**
     * @param null|string|float $quantity
     * @param float $expectedValue
     * @dataProvider getQuantityDataProvider
     */
    public function testGetQuantity($quantity, $expectedValue)
    {
        $tierPrice = new TierPrice(
            $this->product,
            $quantity,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->session,
            $this->groupManagement,
            $this->customerGroupRetriever,
            $this->scopeConfigMock
        );

        $this->assertEquals($expectedValue, $tierPrice->getQuantity());
    }

    /**
     * @return array
     */
    public static function getQuantityDataProvider()
    {
        return [
            [null, 1],
            ['one', 1],
            ['', 1],
            [4, 4],
            [4.5, 4.5],
            ['0.7', 0.7],
            ['0.0000000', 1]
        ];
    }
}
