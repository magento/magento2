<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Type;

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Customer\Model\GroupManagement;

/**
 * Price Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends \PHPUnit\Framework\TestCase
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

    private $tierPriceExtensionFactoryMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->product = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\Product::class);

        $this->tpFactory = $this->createPartialMock(
            \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory::class,
            ['create']
        );

        $this->websiteMock = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getId']);
        $storeMangerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
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
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getValue']
        );

        $group = $this->createMock(\Magento\Customer\Model\Data\Group::class);
        $group->expects($this->any())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagementMock =
            $this->createMock(\Magento\Customer\Api\GroupManagementInterface::class);
        $this->groupManagementMock->expects($this->any())->method('getAllCustomersGroup')
            ->will($this->returnValue($group));
        $this->tierPriceExtensionFactoryMock = $this->getMockBuilder(ProductTierPriceExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\Type\Price::class,
            [
                'tierPriceFactory' => $this->tpFactory,
                'config' => $this->scopeConfigMock,
                'storeManager' => $storeMangerMock,
                'groupManagement' => $this->groupManagementMock,
                'tierPriceExtensionFactory' => $this->tierPriceExtensionFactoryMock
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
        $this->scopeConfigMock->expects($this->any())->method('getValue')->will($this->returnValue($priceScope));
        $this->websiteMock->expects($this->any())->method('getId')->will($this->returnValue($expectedWebsiteId));
        $this->tpFactory->expects($this->any())
            ->method('create')
            ->will(
                $this->returnCallback(
                    function () {
                        return $this->objectManagerHelper->getObject(\Magento\Catalog\Model\Product\TierPrice::class);
                    }
                )
            );

        // create sample TierPrice objects that would be coming from a REST call
        $tierPriceExtensionMock = $this->getMockBuilder(ProductTierPriceExtensionInterface::class)
            ->setMethods(['getWebsiteId', 'setWebsiteId', 'getPercentageValue', 'setPercentageValue'])
            ->getMock();
        $tierPriceExtensionMock->expects($this->any())->method('getWebsiteId')->willReturn($expectedWebsiteId);
        $tierPriceExtensionMock->expects($this->any())->method('getPercentageValue')->willReturn(null);
        $tp1 = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\Product\TierPrice::class);
        $tp1->setValue(10);
        $tp1->setCustomerGroupId(1);
        $tp1->setQty(11);
        $tp1->setExtensionAttributes($tierPriceExtensionMock);
        $tp2 = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\Product\TierPrice::class);
        $tp2->setValue(20);
        $tp2->setCustomerGroupId(2);
        $tp2->setQty(22);
        $tp2->setExtensionAttributes($tierPriceExtensionMock);
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
        $this->assertEquals(count($tps), count($tpArray));

        $count = count($tps);
        for ($i = 0; $i < $count; $i++) {
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

        $tierPriceExtensionMock = $this->getMockBuilder(ProductTierPriceExtensionInterface::class)
            ->setMethods(['getWebsiteId', 'setWebsiteId', 'getPercentageValue', 'setPercentageValue'])
            ->getMock();
        $tierPriceExtensionMock->expects($this->any())->method('getPercentageValue')->willReturn(50);
        $tierPriceExtensionMock->expects($this->any())->method('setWebsiteId');
        $this->tierPriceExtensionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($tierPriceExtensionMock);

        // test with the data retrieved as a REST object
        $tpRests = $this->model->getTierPrices($this->product);
        $this->assertNotNull($tpRests);
        $this->assertTrue(is_array($tpRests));
        $this->assertEquals(count($tps), count($tpRests));
        foreach ($tpRests as $tpRest) {
            $this->assertEquals(50, $tpRest->getExtensionAttributes()->getPercentageValue());
        }

        $count = count($tps);
        for ($i = 0; $i < $count; $i++) {
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
