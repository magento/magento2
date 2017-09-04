<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\GroupManagement;

/**
 * Test for \Magento\Catalog\Pricing\Price\TierPrice
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test customer group
     *
     * @var int
     */
    private $customerGroup = Group::NOT_LOGGED_IN_ID;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $priceInfo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var float
     */
    private $quantity = 3.;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $calculator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var TierPrice
     */
    private $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManagement;

    /**
     * @var \Magento\Customer\Model\Group\RetrieverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupRetriever;

    /**
     * Initialize base dependencies
     */
    protected function setUp()
    {
        $this->priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);

        $this->product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getPriceInfo', 'hasCustomerGroupId', 'getCustomerGroupId', 'getResource', '__wakeup']);
        $this->product->expects($this->any())->method('getPriceInfo')->will($this->returnValue($this->priceInfo));
        $this->customerGroupRetriever = $this->getMockBuilder(\Magento\Customer\Model\Group\RetrieverInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->session = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->session->expects($this->any())->method('getCustomerGroupId')
            ->will($this->returnValue($this->customerGroup));
        $this->customerGroupRetriever = $this->createMock(\Magento\Customer\Model\Group\RetrieverInterface::class);
        $this->calculator = $this->createMock(\Magento\Framework\Pricing\Adjustment\Calculator::class);
        $this->groupManagement = $this->createMock(\Magento\Customer\Api\GroupManagementInterface::class);

        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->model = new TierPrice(
            $this->product,
            $this->quantity,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->session,
            $this->groupManagement,
            $this->customerGroupRetriever
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
            ->will($this->returnCallback(
                function ($arg) {
                    return $arg -1;
                }
            )
            );
        $this->product->setData(TierPrice::PRICE_CODE, $tierPrices);
        $group = $this->createMock(\Magento\Customer\Model\Data\Group::class);
        $group->expects($this->any())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagement->expects($this->any())->method('getAllCustomersGroup')->will($this->returnValue($group));
        $this->assertEquals($convertedExpectedValue, $this->model->getValue());
    }

    /**
     * @return array
     */
    public function providerForBaseInitialization()
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
                        'cust_group' => $this->customerGroup + 1
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
                        'cust_group' => $this->customerGroup
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
            ->will($this->returnValue(true));
        $this->product->expects($this->once())->method('getCustomerGroupId')
            ->will($this->returnValue($this->customerGroup));

        $backendMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class);

        $attributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $attributeMock->expects($this->once())->method('getBackend')->will($this->returnValue($backendMock));

        $productResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productResource->expects($this->once())->method('getAttribute')->with(TierPrice::PRICE_CODE)
            ->will($this->returnValue($attributeMock));

        $this->product->expects($this->once())->method('getResource')->will($this->returnValue($productResource));

        $tierPrice = new TierPrice(
            $this->product,
            $this->quantity,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->session,
            $this->groupManagement,
            $this->customerGroupRetriever
        );
        $group = $this->createMock(\Magento\Customer\Model\Data\Group::class);
        $group->expects($this->once())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagement->expects($this->once())->method('getAllCustomersGroup')
            ->will($this->returnValue($group));

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

        $price = $this->createMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
        $price->expects($this->any())->method('getValue')->will($this->returnValue($basePrice));

        $this->calculator->expects($this->atLeastOnce())->method('getAmount')
            ->will($this->returnArgument(0));

        $this->priceInfo->expects(static::atLeastOnce())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($price);
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->will($this->returnCallback(
                function ($arg) {
                    return round(0.5 * $arg, 2);
                }
            )
            );

        $group = $this->createMock(\Magento\Customer\Model\Data\Group::class);
        $group->expects($this->any())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagement->expects($this->any())->method('getAllCustomersGroup')
            ->will($this->returnValue($group));
        $this->assertEquals($expectedResult, $this->model->getTierPriceList());
        $this->assertEquals(count($expectedResult), $this->model->getTierPriceCount());
        //Second call will get the cached value
        $this->assertEquals($expectedResult, $this->model->getTierPriceList());
        $this->assertEquals(count($expectedResult), $this->model->getTierPriceCount());
    }

    /**
     * @return array
     */
    public function providerForGetterTierPriceList()
    {
        return [
            'base case' => [
                'tierPrices' => [
                    // will be ignored due to customer group
                    [
                        'price'         => '1.3',
                        'website_price' => '1.3',
                        'price_qty'     => '1.3',
                        'cust_group'    => $this->customerGroup + 1
                    ],
                    [
                        'price'         => '25.4',
                        'website_price' => '25.4',
                        'price_qty'     => '5.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                    // cases to calculate save percent
                    [
                        'price'         => '15.1',
                        'website_price' => '15.1',
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
                        'price'          => '7.55',
                        'website_price'  => '7.55',
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
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::__construct
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::getSavePercent
     * @covers \Magento\Catalog\Pricing\Price\TierPrice::getBasePrice
     * @dataProvider dataProviderGetSavePercent
     */
    public function testGetSavePercent($basePrice, $tierPrice, $savedPercent)
    {
        $price = $this->createMock(\Magento\Framework\Pricing\Price\PriceInterface::class);

        $this->priceInfo->expects(static::atLeastOnce())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($price);
        $price->expects(static::atLeastOnce())
            ->method('getValue')
            ->willReturn($basePrice);

        $amount = $this->getMockForAbstractClass(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $amount->expects($this->atLeastOnce())
            ->method('getBaseAmount')
            ->will($this->returnValue($tierPrice));

        $this->assertEquals($savedPercent, $this->model->getSavePercent($amount));
    }

    /**
     * @return array
     */
    public function dataProviderGetSavePercent()
    {
        return [
            ['basePrice' => '100', 'tierPrice' => '90', 'savedPercent' => '10'],
            ['basePrice' => '70', 'tierPrice' => '35', 'savedPercent' => '50'],
            ['basePrice' => '50', 'tierPrice' => '35', 'savedPercent' => '30'],
            ['basePrice' => '20.80', 'tierPrice' => '18.72', 'savedPercent' => '10']
        ];
    }
}
