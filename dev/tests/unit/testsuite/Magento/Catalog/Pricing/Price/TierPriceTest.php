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

namespace Magento\Catalog\Pricing\Price;

use Magento\Customer\Model\Group;

/**
 * Test for \Magento\Catalog\Pricing\Price\TierPrice
 */
class TierPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test customer group
     *
     * @var int
     */
    protected $customerGroup = Group::NOT_LOGGED_IN_ID;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var float
     */
    protected $quantity = 3.;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var TierPrice
     */
    protected $model;

    /**
     * Initialize base dependencies
     */
    protected function setUp()
    {
        $this->priceInfo = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);

        $this->product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getPriceInfo', 'hasCustomerGroupId', 'getCustomerGroupId', 'getResource', '__wakeup'],
            [],
            '',
            false
        );
        $this->product->expects($this->any())->method('getPriceInfo')->will($this->returnValue($this->priceInfo));

        $this->session = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->session->expects($this->any())->method('getCustomerGroupId')
            ->will($this->returnValue($this->customerGroup));

        $this->calculator = $this->getMock('Magento\Framework\Pricing\Adjustment\Calculator', [], [], '', false);

        $this->model = new TierPrice($this->product, $this->quantity, $this->calculator, $this->session);
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
        $this->product->setData(TierPrice::PRICE_CODE, $tierPrices);
        $this->assertEquals($expectedValue, $this->model->getValue());
    }

    /**
     * @return array
     */
    public function providerForBaseInitialization()
    {
        return [
            'case for getValue' => [
                'tierPrices' => [
                    ['website_price' => '20.', 'price_qty' => '1.', 'cust_group' => Group::CUST_GROUP_ALL],
                    ['website_price' => '10.', 'price_qty' => '1.', 'cust_group' => Group::CUST_GROUP_ALL],
                ],
                'expectedValue' => 10.
            ],
            'case for canApplyTierPrice' => [
                'tierPrices' => [
                    // tier not for current customer group
                    ['website_price' => '10.', 'price_qty' => '1.', 'cust_group' => $this->customerGroup + 1],
                    // tier is higher than product qty
                    ['website_price' => '10.', 'price_qty' => '10.', 'cust_group' => Group::CUST_GROUP_ALL],
                    // higher tier qty already found
                    ['website_price' => '10.', 'price_qty' => '0.5', 'cust_group' => Group::CUST_GROUP_ALL],
                    // found tier qty is same as current tier qty but current tier group is ALL_GROUPS
                    ['website_price' => '5.', 'price_qty' => '1.', 'cust_group' => $this->customerGroup],
                    ['website_price' => '1.', 'price_qty' => '1.', 'cust_group' => Group::CUST_GROUP_ALL],
                ],
                'expectedValue' => 5.
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

        $backendMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend', [], [], '', false);

        $attributeMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\AbstractAttribute', [], [], '', false);
        $attributeMock->expects($this->once())->method('getBackend')->will($this->returnValue($backendMock));

        $productResource = $this->getMock('Magento\Catalog\Model\Resource\Product', [], [], '', false);
        $productResource->expects($this->once())->method('getAttribute')->with(TierPrice::PRICE_CODE)
            ->will($this->returnValue($attributeMock));

        $this->product->expects($this->once())->method('getResource')->will($this->returnValue($productResource));

        $tierPrice = new TierPrice($this->product, $this->quantity, $this->calculator, $this->session);
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

        $price = $this->getMock('Magento\Framework\Pricing\Price\PriceInterface', [], [], '', false);
        $price->expects($this->any())->method('getValue')->will($this->returnValue($basePrice));

        $this->priceInfo->expects($this->atLeastOnce())->method('getPrice')->will($this->returnValue($price));

        $this->calculator->expects($this->atLeastOnce())->method('getAmount')
            ->will($this->returnArgument(0));

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
                    // will be ignored due to bigger price
                    [
                        'price'         => '50.3',
                        'website_price' => '50.3',
                        'price_qty'     => '10.3',
                        'cust_group'    => Group::CUST_GROUP_ALL
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
                        'price'          => '15.1',
                        'website_price'  => '15.1',
                        'price_qty'      => '5.',
                        'cust_group'     => Group::CUST_GROUP_ALL
                    ],
                    [
                        'price'         => '8.3',
                        'website_price' => '8.3',
                        'price_qty'     => '2.',
                        'cust_group'    => Group::CUST_GROUP_ALL
                    ],
                ]
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
        $priceAmount = $this->getMockForAbstractClass('Magento\Framework\Pricing\Amount\AmountInterface');
        $priceAmount->expects($this->once())
            ->method('getBaseAmount')
            ->will($this->returnValue($basePrice));
        
        $price = $this->getMock('Magento\Framework\Pricing\Price\PriceInterface');
        $price->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($priceAmount));

        $this->priceInfo->expects($this->atLeastOnce())
            ->method('getPrice')
            ->will($this->returnValue($price));

        $amount = $this->getMockForAbstractClass('Magento\Framework\Pricing\Amount\AmountInterface');
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
        return array(
            ['basePrice' => '100', 'tierPrice' => '90', 'savedPercent' => '10'],
            ['basePrice' => '70', 'tierPrice' => '35', 'savedPercent' => '50'],
            ['basePrice' => '50', 'tierPrice' => '35', 'savedPercent' => '30']
        );
    }
}
