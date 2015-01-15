<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Exception\NoSuchEntityException;

class TierPriceManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TierPriceManagement
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceModifierMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

    protected function setUp()
    {
        $this->repositoryMock = $this->getMock(
            '\Magento\Catalog\Model\ProductRepository',
            [],
            [],
            '',
            false
        );
        $this->priceBuilderMock = $this->getMock(
            'Magento\Catalog\Api\Data\ProductTierPriceDataBuilder',
            ['populateWithArray', 'create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->websiteMock =
            $this->getMock('Magento\Store\Model\Website', ['getId', '__wakeup'], [], '', false);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getData', 'getIdBySku', 'load', '__wakeup', 'save', 'validate', 'setData'],
            [],
            '',
            false
        );
        $this->configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->priceModifierMock =
            $this->getMock('Magento\Catalog\Model\Product\PriceModifier', [], [], '', false);
        $this->repositoryMock->expects($this->any())->method('get')->with('product_sku')
            ->will($this->returnValue($this->productMock));
        $this->groupManagementMock =
            $this->getMock('Magento\Customer\Api\GroupManagementInterface', [], [], '', false);
        $this->groupRepositoryMock =
            $this->getMock('Magento\Customer\Api\GroupRepositoryInterface', [], [], '', false);

        $this->service = new TierPriceManagement(
            $this->repositoryMock,
            $this->priceBuilderMock,
            $this->storeManagerMock,
            $this->priceModifierMock,
            $this->configMock,
            $this->groupManagementMock,
            $this->groupRepositoryMock
        );
    }

    /**
     * @param $configValue
     * @param $customerGroupId
     * @param $groupData
     * @param $expected
     * @dataProvider getListDataProvider
     */
    public function testGetList($configValue, $customerGroupId, $groupData, $expected)
    {
        $this->repositoryMock->expects($this->once())->method('get')->with('product_sku')
            ->will($this->returnValue($this->productMock));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue([$groupData]));
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue($configValue));
        if ($expected) {
            $this->priceBuilderMock
                ->expects($this->once())
                ->method('populateWithArray')
                ->with($expected);
            $this->priceBuilderMock
                ->expects($this->once())
                ->method('create')
                ->will($this->returnValue('data'));
        } else {
            $this->priceBuilderMock->expects($this->never())->method('populateWithArray');
        }
        $prices = $this->service->getList('product_sku', $customerGroupId);
        $this->assertCount($expected ? 1 : 0, $prices);
        if ($expected) {
            $this->assertEquals('data', $prices[0]);
        }
    }

    public function getListDataProvider()
    {
        return [
            [
                1,
                'all',
                ['website_price' => 10, 'price' => 5, 'all_groups' => 1, 'price_qty' => 5],
                ['value' => 10, 'qty' => 5],
            ],
            [
                0,
                1,
                ['website_price' => 10, 'price' => 5, 'all_groups' => 0, 'cust_group' => 1, 'price_qty' => 5],
                ['value' => 5, 'qty' => 5]
            ],
            [
                0,
                'all',
                ['website_price' => 10, 'price' => 5, 'all_groups' => 0, 'cust_group' => 1, 'price_qty' => 5],
                []
            ]
        ];
    }

    public function testSuccessDeleteTierPrice()
    {
        $this->storeManagerMock
            ->expects($this->never())
            ->method('getWebsite');
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(0));
        $this->priceModifierMock->expects($this->once())->method('removeTierPrice')->with($this->productMock, 4, 5, 0);

        $this->assertEquals(true, $this->service->remove('product_sku', 4, 5, 0));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @message Such product doesn't exist
     */
    public function testDeleteTierPriceFromNonExistingProduct()
    {
        $this->repositoryMock->expects($this->once())->method('get')
            ->will($this->throwException(new NoSuchEntityException()));
        $this->priceModifierMock->expects($this->never())->method('removeTierPrice');
        $this->storeManagerMock
            ->expects($this->never())
            ->method('getWebsite');
        $this->service->remove('product_sku', null, 10, 5);
    }

    public function testSuccessDeleteTierPriceFromWebsiteLevel()
    {
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($this->websiteMock));
        $this->websiteMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(1));
        $this->priceModifierMock->expects($this->once())->method('removeTierPrice')->with($this->productMock, 4, 5, 1);

        $this->assertEquals(true, $this->service->remove('product_sku', 4, 5, 6));
    }

    public function testSetNewPriceWithGlobalPriceScopeAll()
    {
        $websiteMock = $this->getMockBuilder('Magento\Store\Model\Website')
            ->setMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->once())->method('getId')->will($this->returnValue(0));

        $this->storeManagerMock->expects($this->once())->method('getWebsite')->will($this->returnValue($websiteMock));

        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will(
                $this->returnValue(
                    [['all_groups' => 0, 'website_id' => 0, 'price_qty' => 4, 'price' => 50]]
                )
            );
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(1));

        $this->productMock->expects($this->once())->method('setData')->with(
            'tier_price',
            [
                ['all_groups' => 0, 'website_id' => 0, 'price_qty' => 4, 'price' => 50],
                [
                    'cust_group' => 32000,
                    'price' => 100,
                    'website_price' => 100,
                    'website_id' => 0,
                    'price_qty' => 3
                ]
            ]
        );
        $this->repositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $group = $this->getMock('\Magento\Customer\Model\Data\Group',
            [],
            [],
            '',
            false
        );
        $group->expects($this->once())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagementMock->expects($this->once())->method('getAllCustomersGroup')
            ->will($this->returnValue($group));
        $this->service->add('product_sku', 'all', 100, 3);
    }

    public function testSetNewPriceWithGlobalPriceScope()
    {
        $group = $this->getMock('\Magento\Customer\Model\Data\Group', [], [], '', false);
        $group->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->groupRepositoryMock->expects($this->once())->method('getById')->will($this->returnValue($group));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will(
                $this->returnValue(
                    [['cust_group' => 1, 'website_id' => 0, 'price_qty' => 4, 'price' => 50]]
                )
            );
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(0));

        $this->productMock->expects($this->once())->method('setData')->with(
            'tier_price',
            [
                ['cust_group' => 1, 'website_id' => 0, 'price_qty' => 4, 'price' => 50],
                ['cust_group' => 1, 'website_id' => 0, 'price_qty' => 3, 'price' => 100, 'website_price' => 100]
            ]
        );
        $this->repositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->service->add('product_sku', 1, 100, 3);
    }

    public function testSetUpdatedPriceWithGlobalPriceScope()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will(
                $this->returnValue(
                    [['cust_group' => 1, 'website_id' => 0, 'price_qty' => 3, 'price' => 50]]
                )
            );
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(0));

        $this->productMock->expects($this->once())->method('setData')->with(
            'tier_price',
            [
                ['cust_group' => 1, 'website_id' => 0, 'price_qty' => 3, 'price' => 100]
            ]
        );
        $this->repositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->service->add('product_sku', 1, 100, 3);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Values of following attributes are invalid: attr1, attr2
     */
    public function testSetThrowsExceptionIfDoesntValidate()
    {
        $group = $this->getMock('\Magento\Customer\Model\Data\Group', [], [], '', false);
        $group->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue([]));

        $this->groupRepositoryMock->expects($this->once())->method('getById')->will($this->returnValue($group));
        $this->productMock->expects($this->once())->method('validate')->will(
            $this->returnValue(
                ['attr1' => '', 'attr2' => '']
            )
        );
        $this->repositoryMock->expects($this->never())->method('save');
        $this->service->add('product_sku', 1, 100, 2);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSetThrowsExceptionIfCantSave()
    {
        $group = $this->getMock('\Magento\Customer\Model\Data\Group', [], [], '', false);
        $group->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue([]));

        $this->groupRepositoryMock->expects($this->once())->method('getById')->will($this->returnValue($group));
        $this->repositoryMock->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $this->service->add('product_sku', 1, 100, 2);
    }

    /**
     * @param string|int $price
     * @param string|float $qty
     * @expectedException \Magento\Framework\Exception\InputException
     * @dataProvider addDataProvider
     */

    public function testAddWithInvalidData($price, $qty)
    {
        $this->service->add('product_sku', 1, $price, $qty);
    }

    public function addDataProvider()
    {
        return [
            ['string', 10],
            [10, '10string'],
            [10, -15]
        ];
    }
}
