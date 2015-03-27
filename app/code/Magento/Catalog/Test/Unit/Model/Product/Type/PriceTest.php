<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Price Test
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    const KEY_GROUP_PRICE = 'group_price';
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
     * @var \Magento\Catalog\Api\Data\ProductGroupPriceInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gpFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->product = $this->objectManagerHelper->getObject('Magento\Catalog\Model\Product');

        $this->gpFactory = $this->getMockForAbstractClass(
            'Magento\Catalog\Api\Data\ProductGroupPriceInterfaceFactory',
            [],
            '',
            false,
            true,
            true,
            ['create']
        );

        $this->websiteMock = $this->getMock('Magento\Store\Model\Website', ['getId'], [], '', false);
        $storeManger = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['getWebsite']
        );
        $storeManger->expects($this->any())
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

        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Product\Type\Price',
            [
                'groupPriceFactory' => $this->gpFactory,
                'config' => $this->scopeConfigMock,
                'storeManager' => $storeManger
            ]
        );
    }

    public function testGetGroupPricesWithNull()
    {
        // test when we don't send anything in, that no data changes
        $someValue = 'any fake value';
        $this->product->setData($this::KEY_GROUP_PRICE, $someValue);
        $this->assertEquals($someValue, $this->product->getData($this::KEY_GROUP_PRICE));

        $this->model->setGroupPrices($this->product, null);
        $this->assertEquals($someValue, $this->product->getData($this::KEY_GROUP_PRICE));
    }

    /**
     * testGetGroupPrices
     * testSetGroupPrices
     *
     * @dataProvider groupPricesDataProvider
     */
    public function testGroupPrices($priceScope, $expectedWebsiteId)
    {
        // establish the behavior of the mocks
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($priceScope));
        $this->websiteMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($expectedWebsiteId));
        $this->gpFactory->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function () {
                return $this->objectManagerHelper->getObject('Magento\Catalog\Model\Product\GroupPrice');
            }));

        // create sample GroupPrice objects that would be coming from a REST call
        $gp1 = $this->objectManagerHelper->getObject('Magento\Catalog\Model\Product\GroupPrice');
        $gp1->setValue(10);
        $gp1->setCustomerGroupId(1);
        $gp2 = $this->objectManagerHelper->getObject('Magento\Catalog\Model\Product\GroupPrice');
        $gp2->setValue(20);
        $gp2->setCustomerGroupId(2);
        $gps = [$gp1, $gp2];

        // force the product to have null group prices
        $this->product->setData($this::KEY_GROUP_PRICE, null);
        $this->assertNull($this->product->getData($this::KEY_GROUP_PRICE));

        // set the product with the GroupPrice objects
        $this->model->setGroupPrices($this->product, $gps);

        // test the data actually set on the product
        $gpArray = $this->product->getData($this::KEY_GROUP_PRICE);
        $this->assertNotNull($gpArray);
        $this->assertTrue(is_array($gpArray));
        $this->assertEquals(sizeof($gps), sizeof($gpArray));

        for ($i = 0; $i < sizeof($gps); $i++) {
            $gpData = $gpArray[$i];
            $this->assertEquals($expectedWebsiteId, $gpData['website_id'], 'Website Id does not match');
            $this->assertEquals($gps[$i]->getValue(), $gpData['price'], 'Price/Value does not match');
            $this->assertEquals($gps[$i]->getValue(), $gpData['website_price'], 'WebsitePrice/Value does not match');
            $this->assertEquals(
                $gps[$i]->getCustomerGroupId(),
                $gpData['cust_group'],
                'Customer group Id does not match'
            );
        }

        // test with the data retrieved as a REST object
        $gpRests = $this->model->getGroupPrices($this->product);
        $this->assertNotNull($gpRests);
        $this->assertTrue(is_array($gpRests));
        $this->assertEquals(sizeof($gps), sizeof($gpRests));

        for ($i = 0; $i < sizeof($gps); $i++) {
            $this->assertEquals(
                $gps[$i]->getValue(),
                $gpRests[$i]->getValue(),
                'REST: Price/Value does not match'
            );
            $this->assertEquals(
                $gps[$i]->getCustomerGroupId(),
                $gpRests[$i]->getCustomerGroupId(),
                'REST: Customer group Id does not match'
            );
        }
    }

    /**
     * @return array
     */
    public function groupPricesDataProvider()
    {
        return [
            'global price scope' => [$this::PRICE_SCOPE_GLOBAL, 0],
            'website price scope' => [$this::PRICE_SCOPE_WEBSITE, 2]
        ];
    }
}