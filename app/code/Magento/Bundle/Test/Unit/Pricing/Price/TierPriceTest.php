<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Pricing\Price\TierPrice;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Data\Group as DataGroup;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[CoversClass(TierPrice::class)]
class TierPriceTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var MockObject
     */
    protected $priceInfo;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var MockObject
     */
    protected $calculator;

    /**
     * @var TierPrice
     */
    protected $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var MockObject
     */
    protected $groupManagement;

    /**
     * Initialize base dependencies
     */
    protected function setUp(): void
    {
        $this->priceInfo = $this->createMock(Base::class);

        $this->product = $this->createPartialMockWithReflection(
            Product::class,
            ['getPriceInfo', 'getResource']
        );
        $this->product->method('getPriceInfo')->willReturn($this->priceInfo);
        
        // Configure getResource to return a mock with getAttribute method
        $resource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $resource->method('getAttribute')->willReturn(
            $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
        );
        $this->product->method('getResource')->willReturn($resource);

        $this->calculator = $this->createMock(Calculator::class);
        $this->groupManagement = $this->createMock(GroupManagementInterface::class);

        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $objectHelper = new ObjectManager($this);
        $this->model = $objectHelper->getObject(
            TierPrice::class,
            [
                'saleableItem' => $this->product,
                'calculator' => $this->calculator,
                'priceCurrency' => $this->priceCurrencyMock,
                'groupManagement' => $this->groupManagement
            ]
        );
    }

    #[DataProvider('providerForGetterTierPriceList')]
    public function testGetterTierPriceList($tierPrices, $basePrice, $expectedResult)
    {
        $this->product->setData(TierPrice::PRICE_CODE, $tierPrices);

        $price = $this->createMock(PriceInterface::class);
        $price->method('getValue')->willReturn($basePrice);

        $this->priceInfo->method('getPrice')->willReturn($price);

        $this->calculator->expects($this->atLeastOnce())
            ->method('getAmount')
            ->willReturnArgument(0);

        $this->priceCurrencyMock->expects($this->never())->method('convertAndRound');

        $group = $this->createMock(DataGroup::class);
        $group->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagement->method('getAllCustomersGroup')->willReturn($group);
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

    #[DataProvider('providerForTestGetSavePercent')]
    public function testGetSavePercent($baseAmount, $tierPrice, $savePercent)
    {
        /** @var AmountInterface|MockObject $amount */
        $amount = $this->createMock(AmountInterface::class);
        $amount->method('getValue')->willReturn($tierPrice);

        $priceAmount = $this->createMock(AmountInterface::class);
        $priceAmount->method('getValue')->willReturn($baseAmount);

        $price = $this->createMock(PriceInterface::class);
        $price->method('getAmount')->willReturn($priceAmount);

        $this->priceInfo->method('getPrice')->willReturn($price);

        $this->assertEquals($savePercent, $this->model->getSavePercent($amount));
    }

    /**
     * @return array
     */
    public static function providerForTestGetSavePercent()
    {
        return [
            'no fraction' => [9.0000, 8.1, 10],
            'lower half'  => [9.1234, 8.3, 9],
        ];
    }
}
