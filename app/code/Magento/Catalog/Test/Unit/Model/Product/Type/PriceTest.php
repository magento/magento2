<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Type;

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\TierPrice;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Data\Group;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends TestCase
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
     * @var Product
     */
    protected $product;

    /**
     * @var ProductTierPriceInterfaceFactory|MockObject
     */
    protected $tpFactory;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var GroupManagementInterface|MockObject
     */
    protected $groupManagementMock;

    /**
     * @var Website|MockObject
     */
    protected $websiteMock;

    /**
     * @var ProductTierPriceExtensionFactory|MockObject
     */
    private $tierPriceExtensionFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->product = $this->objectManagerHelper->getObject(Product::class);

        $this->tpFactory = $this->createPartialMock(
            ProductTierPriceInterfaceFactory::class,
            ['create']
        );

        $this->websiteMock = $this->createPartialMock(Website::class, ['getId']);
        $storeMangerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getWebsite']
        );
        $storeMangerMock->expects($this->any())
            ->method('getWebsite')
            ->willReturn($this->websiteMock);

        $this->scopeConfigMock = $this->getMockForAbstractClass(
            ScopeConfigInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getValue']
        );

        $group = $this->createMock(Group::class);
        $group->expects($this->any())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagementMock =
            $this->getMockForAbstractClass(GroupManagementInterface::class);
        $this->groupManagementMock->expects($this->any())->method('getAllCustomersGroup')
            ->willReturn($group);
        $this->tierPriceExtensionFactoryMock = $this->getMockBuilder(ProductTierPriceExtensionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManagerHelper->getObject(
            Price::class,
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
     * @return void
     * @dataProvider nullPricesDataProvider
     */
    public function testGetPricesWithNull($key, $getter): void
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
    public function nullPricesDataProvider(): array
    {
        return [
            'testGetTierPricesWithNull' => [$this::KEY_TIER_PRICE, 'setTierPrices']
        ];
    }

    /**
     * @return array
     */
    public function pricesDataProvider(): array
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
     * @return void
     * @dataProvider pricesDataProvider
     */
    public function testTierPrices($priceScope, $expectedWebsiteId): void
    {
        // establish the behavior of the mocks
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn($priceScope);
        $this->websiteMock->expects($this->any())->method('getId')->willReturn($expectedWebsiteId);
        $this->tpFactory->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return $this->objectManagerHelper->getObject(TierPrice::class);
                }
            );

        // create sample TierPrice objects that would be coming from a REST call
        $tierPriceExtensionMock = $this->getProductTierPriceExtensionInterfaceMock();
        $tierPriceExtensionMock->expects($this->any())->method('getWebsiteId')->willReturn($expectedWebsiteId);
        $tierPriceExtensionMock->expects($this->any())->method('getPercentageValue')->willReturn(null);
        $tp1 = $this->objectManagerHelper->getObject(TierPrice::class);
        $tp1->setValue(10);
        $tp1->setCustomerGroupId(1);
        $tp1->setQty(11);
        $tp1->setExtensionAttributes($tierPriceExtensionMock);
        $tp2 = $this->objectManagerHelper->getObject(TierPrice::class);
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
        $this->assertIsArray($tpArray);
        $this->assertCount(count($tps), $tpArray);

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

        $tierPriceExtensionMock = $this->getProductTierPriceExtensionInterfaceMock();
        $tierPriceExtensionMock->expects($this->any())->method('getPercentageValue')->willReturn(50);
        $tierPriceExtensionMock->expects($this->any())->method('setWebsiteId');
        $this->tierPriceExtensionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($tierPriceExtensionMock);

        // test with the data retrieved as a REST object
        $tpRests = $this->model->getTierPrices($this->product);
        $this->assertNotNull($tpRests);
        $this->assertIsArray($tpRests);
        $this->assertCount(count($tps), $tpRests);
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

    /**
     * Get tier price with percent value type.
     *
     * @return void
     */
    public function testGetPricesWithPercentType(): void
    {
        $tierPrices = [
            0 => [
                'record_id' => 0,
                'cust_group' => 3200,
                'price_qty' => 3,
                'website_id' => 0,
                'value_type' => 'percent',
                'percentage_value' => 10,
                ],
        ];
        $this->product->setData('tier_price', $tierPrices);
        $this->tpFactory->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return $this->objectManagerHelper->getObject(TierPrice::class);
                }
            );
        $tierPriceExtensionMock = $this->getProductTierPriceExtensionInterfaceMock();
        $tierPriceExtensionMock->method('getPercentageValue')
            ->willReturn(50);
        $this->tierPriceExtensionFactoryMock->method('create')
            ->willReturn($tierPriceExtensionMock);

        $this->assertInstanceOf(TierPrice::class, $this->model->getTierPrices($this->product)[0]);
    }

    /**
     * Build ProductTierPriceExtensionInterface mock.
     *
     * @return MockObject
     */
    private function getProductTierPriceExtensionInterfaceMock(): MockObject
    {
        $mockBuilder = $this->getMockBuilder(ProductTierPriceExtensionInterface::class)
            ->disableOriginalConstructor();
        try {
            $mockBuilder->addMethods(['getPercentageValue', 'setPercentageValue', 'setWebsiteId', 'getWebsiteId']);
        } catch (RuntimeException $e) {
            // ProductTierPriceExtensionInterface already generated and has all necessary methods.
        }

        return $mockBuilder->getMock();
    }
}
