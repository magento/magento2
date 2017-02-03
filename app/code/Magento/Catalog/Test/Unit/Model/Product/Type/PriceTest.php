<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Product\Type;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Customer\Model\GroupManagement;

/**
 * Price Test
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    const KEY_TIER_PRICE = 'tier_price';
    const PRICE_SCOPE_GLOBAL = 0;
    const PRICE_SCOPE_WEBSITE = 1;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Price
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tpFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupManagementMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->product = $this->objectManagerHelper->getObject('Magento\Catalog\Model\Product');

        $this->tpFactory = $this->getMockForAbstractClass(
            'Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory',
            [],
            '',
            false,
            true,
            true,
            ['create']
        );

        $this->websiteMock = $this->getMock('Magento\Store\Model\Website', ['getId'], [], '', false);
        $storeMangerMock = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['getWebsite']
        );
        $storeMangerMock->expects($this->any())
            ->method('getWebsite')
            ->will($this->returnValue($this->websiteMock));

        $this->scopeConfigMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false,
            true,
            true,
            ['getValue']
        );

        $group = $this->getMock('\Magento\Customer\Model\Data\Group',
            [],
            [],
            '',
            false
        );
        $group->expects($this->any())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagementMock =
            $this->getMock('Magento\Customer\Api\GroupManagementInterface', [], [], '', false);
        $this->groupManagementMock->expects($this->any())->method('getAllCustomersGroup')
            ->will($this->returnValue($group));

        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Product\Type\Price',
            [
                'tierPriceFactory' => $this->tpFactory,
                'config' => $this->scopeConfigMock,
                'storeManager' => $storeMangerMock,
                'groupManagement' => $this->groupManagementMock
            ]
        );
    }

    /**
     * testGetTierPricesWithNull
     *
     * @dataProvider nullPricesDataProvider
     */
    public function testGetPricesWithNull($key, $getter)
    {
        // test when we don't send anything in, that no data changes
        $someValue = 'any fake value';
        $this->product->setData($key, $someValue);
        $this->assertEquals($someValue, $this->product->getData($key));

        $this->model->$getter($this->product, null);
        $this->assertEquals($someValue, $this->product->getData($key));
    }

    /**
     * @return array
     */
    public function nullPricesDataProvider()
    {
        return [
            'testGetTierPricesWithNull' => [$this::KEY_TIER_PRICE, 'setTierPrices']
        ];
    }

    /**
     * @return array
     */
    public function pricesDataProvider()
    {
        return [
            'global price scope' => [$this::PRICE_SCOPE_GLOBAL, 0],
            'website price scope' => [$this::PRICE_SCOPE_WEBSITE, 2]
        ];
    }

    /**
     * testGetTierPrices
     * testSetTierPrices
     *
     * @dataProvider pricesDataProvider
     */
    public function testTierPrices($priceScope, $expectedWebsiteId)
    {
        // establish the behavior of the mocks
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($priceScope));
        $this->websiteMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($expectedWebsiteId));
        $this->tpFactory->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function () {
                return $this->objectManagerHelper->getObject('Magento\Catalog\Model\Product\TierPrice');
            }));

        // create sample TierPrice objects that would be coming from a REST call
        $tp1 = $this->objectManagerHelper->getObject('Magento\Catalog\Model\Product\TierPrice');
        $tp1->setValue(10);
        $tp1->setCustomerGroupId(1);
        $tp1->setQty(11);
        $tp2 = $this->objectManagerHelper->getObject('Magento\Catalog\Model\Product\TierPrice');
        $tp2->setValue(20);
        $tp2->setCustomerGroupId(2);
        $tp2->setQty(22);
        $tps = [$tp1, $tp2];

        // force the product to have null tier prices
        $this->product->setData($this::KEY_TIER_PRICE, null);
        $this->assertNull($this->product->getData($this::KEY_TIER_PRICE));

        // set the product with the TierPrice objects
        $this->model->setTierPrices($this->product, $tps);

        // test the data actually set on the product
        $tpArray = $this->product->getData($this::KEY_TIER_PRICE);
        $this->assertNotNull($tpArray);
        $this->assertTrue(is_array($tpArray));
        $this->assertEquals(sizeof($tps), sizeof($tpArray));

        for ($i = 0; $i < sizeof($tps); $i++) {
            $tpData = $tpArray[$i];
            $this->assertEquals($expectedWebsiteId, $tpData['website_id'], 'Website Id does not match');
            $this->assertEquals($tps[$i]->getValue(), $tpData['price'], 'Price/Value does not match');
            $this->assertEquals($tps[$i]->getValue(), $tpData['website_price'], 'WebsitePrice/Value does not match');
            $this->assertEquals(
                $tps[$i]->getCustomerGroupId(),
                $tpData['cust_group'],
                'Customer group Id does not match'
            );
            $this->assertEquals($tps[$i]->getQty(), $tpData['price_qty'], 'Qty does not match');
        }

        // test with the data retrieved as a REST object
        $tpRests = $this->model->getTierPrices($this->product);
        $this->assertNotNull($tpRests);
        $this->assertTrue(is_array($tpRests));
        $this->assertEquals(sizeof($tps), sizeof($tpRests));

        for ($i = 0; $i < sizeof($tps); $i++) {
            $this->assertEquals(
                $tps[$i]->getValue(),
                $tpRests[$i]->getValue(),
                'REST: Price/Value does not match'
            );
            $this->assertEquals(
                $tps[$i]->getCustomerGroupId(),
                $tpRests[$i]->getCustomerGroupId(),
                'REST: Customer group Id does not match'
            );
            $this->assertEquals(
                $tps[$i]->getQty(),
                $tpRests[$i]->getQty(),
                'REST: Qty does not match'
            );
        }
    }
}
