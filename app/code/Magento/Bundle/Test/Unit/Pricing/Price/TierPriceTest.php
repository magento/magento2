<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use \Magento\Bundle\Pricing\Price\TierPrice;

use Magento\Customer\Model\Group;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceInfo;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $product;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $calculator;

    /**
     * @var TierPrice
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $groupManagement;

    /**
     * Initialize base dependencies
     */
    protected function setUp(): void
    {
        $this->priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getPriceInfo', 'hasCustomerGroupId', 'getCustomerGroupId', 'getResource', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->product->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfo);

        $this->calculator = $this->createMock(\Magento\Framework\Pricing\Adjustment\Calculator::class);
        $this->groupManagement = $this
            ->createMock(\Magento\Customer\Api\GroupManagementInterface::class);

        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject(
            \Magento\Bundle\Pricing\Price\TierPrice::class,
            [
                'saleableItem' => $this->product,
                'calculator' => $this->calculator,
                'priceCurrency' => $this->priceCurrencyMock,
                'groupManagement' => $this->groupManagement
            ]
        );
    }

    /**
     * @covers \Magento\Bundle\Pricing\Price\TierPrice::isFirstPriceBetter
     * @dataProvider providerForGetterTierPriceList
     */
    public function testGetterTierPriceList($tierPrices, $basePrice, $expectedResult)
    {
        $this->product->setData(TierPrice::PRICE_CODE, $tierPrices);

        $price = $this->createMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
        $price->expects($this->any())
            ->method('getValue')
            ->willReturn($basePrice);

        $this->priceInfo->expects($this->any())
            ->method('getPrice')
            ->willReturn($price);

        $this->calculator->expects($this->atLeastOnce())
            ->method('getAmount')
            ->willReturnArgument(0);

        $this->priceCurrencyMock->expects($this->never())->method('convertAndRound');

        $group = $this->createMock(\Magento\Customer\Model\Data\Group::class);
        $group->expects($this->any())
            ->method('getId')
            ->willReturn(\Magento\Customer\Model\GroupManagement::CUST_GROUP_ALL);
        $this->groupManagement->expects($this->any())->method('getAllCustomersGroup')
            ->willReturn($group);
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
                        'price_qty'     => '1.',
                        'cust_group'    => 999
                    ],
                    [
                        'price'         => '50.',
                        'website_price' => '50.',
                        'price_qty'     => '2.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                    [
                        'price'         => '25.',
                        'website_price' => '25.',
                        'price_qty'     => '5.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                    [
                        'price'         => '15.',
                        'website_price' => '15.',
                        'price_qty'     => '5.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                    [
                        'price'         => '30.',
                        'website_price' => '30.',
                        'price_qty'     => '5.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                    [
                        'price'         => '8.',
                        'website_price' => '8.',
                        'price_qty'     => '11.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                ],
                'basePrice' => 20.,
                'expectedResult' => [
                    [
                        'price'         => '15.',
                        'website_price' => '15.',
                        'price_qty'     => '5.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                    [
                        'price'         => '8.',
                        'website_price' => '8.',
                        'price_qty'     => '11.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                ],
            ]
        ];
    }

    /**
     * @dataProvider providerForTestGetSavePercent
     */
    public function testGetSavePercent($baseAmount, $tierPrice, $savePercent)
    {
        /** @var \Magento\Framework\Pricing\Amount\AmountInterface|\PHPUnit\Framework\MockObject\MockObject $amount */
        $amount = $this->getMockForAbstractClass(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $amount->expects($this->any())
            ->method('getValue')
            ->willReturn($tierPrice);

        $priceAmount = $this->getMockForAbstractClass(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $priceAmount->expects($this->any())
            ->method('getValue')
            ->willReturn($baseAmount);

        $price = $this->createMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
        $price->expects($this->any())
            ->method('getAmount')
            ->willReturn($priceAmount);

        $this->priceInfo->expects($this->any())
            ->method('getPrice')
            ->willReturn($price);

        $this->assertEquals($savePercent, $this->model->getSavePercent($amount));
    }

    /**
     * @return array
     */
    public function providerForTestGetSavePercent()
    {
        return [
            'no fraction' => [9.0000, 8.1, 10],
            'lower half'  => [9.1234, 8.3, 9],
        ];
    }
}
