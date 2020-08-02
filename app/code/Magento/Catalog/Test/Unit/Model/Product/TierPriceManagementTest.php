<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifier;
use Magento\Catalog\Model\Product\TierPriceManagement;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Data\Group;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceManagementTest extends TestCase
{
    /**
     * @var TierPriceManagement
     */
    protected $service;

    /**
     * @var MockObject
     */
    protected $repositoryMock;

    /**
     * @var MockObject
     */
    protected $priceFactoryMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $priceModifierMock;

    /**
     * @var MockObject
     */
    protected $websiteMock;

    /**
     * @var MockObject
     */
    protected $configMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $groupManagementMock;

    /**
     * @var MockObject
     */
    protected $groupRepositoryMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ProductRepository::class);
        $this->priceFactoryMock = $this->createPartialMock(
            ProductTierPriceInterfaceFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->websiteMock =
            $this->createPartialMock(Website::class, ['getId']);
        $this->productMock = $this->createPartialMock(
            Product::class,
            ['getData', 'getIdBySku', 'load', 'save', 'validate', 'setData']
        );
        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->priceModifierMock =
            $this->createMock(PriceModifier::class);
        $this->repositoryMock->expects($this->any())->method('get')->with('product_sku')
            ->willReturn($this->productMock);
        $this->groupManagementMock =
            $this->getMockForAbstractClass(GroupManagementInterface::class);
        $this->groupRepositoryMock =
            $this->getMockForAbstractClass(GroupRepositoryInterface::class);

        $this->service = new TierPriceManagement(
            $this->repositoryMock,
            $this->priceFactoryMock,
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
        $group = $this->createMock(Group::class);
        $group->expects($this->any())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagementMock->expects($this->any())->method('getAllCustomersGroup')
            ->willReturn($group);
        $this->repositoryMock->expects($this->once())->method('get')->with('product_sku')
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn([$groupData]);
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE)
            ->willReturn($configValue);
        if ($expected) {
            $priceMock = $this->getMockForAbstractClass(ProductTierPriceInterface::class);
            $priceMock->expects($this->once())
                ->method('setValue')
                ->with($expected['value'])
                ->willReturnSelf();
            $priceMock->expects($this->once())
                ->method('setQty')
                ->with($expected['qty'])
                ->willReturnSelf();
            $this->priceFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($priceMock);
        } else {
            $this->priceFactoryMock->expects($this->never())->method('create');
        }
        $prices = $this->service->getList('product_sku', $customerGroupId);
        $this->assertCount($expected ? 1 : 0, $prices);
        if ($expected) {
            $this->assertEquals($priceMock, $prices[0]);
        }
    }

    /**
     * @return array
     */
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
            ->with('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE)
            ->willReturn(0);
        $this->priceModifierMock->expects($this->once())->method('removeTierPrice')->with($this->productMock, 4, 5, 0);

        $this->assertTrue($this->service->remove('product_sku', 4, 5, 0));
    }

    public function testDeleteTierPriceFromNonExistingProduct()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity.');
        $this->repositoryMock->expects($this->once())->method('get')
            ->willThrowException(new NoSuchEntityException());
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
            ->willReturn($this->websiteMock);
        $this->websiteMock->expects($this->once())->method('getId')->willReturn(1);
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE)
            ->willReturn(1);
        $this->priceModifierMock->expects($this->once())->method('removeTierPrice')->with($this->productMock, 4, 5, 1);

        $this->assertTrue($this->service->remove('product_sku', 4, 5, 6));
    }

    public function testSetNewPriceWithGlobalPriceScopeAll()
    {
        $websiteMock = $this->getMockBuilder(Website::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->once())->method('getId')->willReturn(0);

        $this->storeManagerMock->expects($this->once())->method('getWebsite')->willReturn($websiteMock);

        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn(
                [['all_groups' => 0, 'website_id' => 0, 'price_qty' => 4, 'price' => 50]]
            );
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE)
            ->willReturn(1);

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
        $group = $this->createMock(Group::class);
        $group->expects($this->once())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagementMock->expects($this->once())->method('getAllCustomersGroup')
            ->willReturn($group);
        $this->service->add('product_sku', 'all', 100, 3);
    }

    public function testSetNewPriceWithGlobalPriceScope()
    {
        $group = $this->createMock(Group::class);
        $group->expects($this->once())->method('getId')->willReturn(1);
        $this->groupRepositoryMock->expects($this->once())->method('getById')->willReturn($group);
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn(
                [['cust_group' => 1, 'website_id' => 0, 'price_qty' => 4, 'price' => 50]]
            );
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE)
            ->willReturn(0);

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
            ->willReturn(
                [['cust_group' => 1, 'website_id' => 0, 'price_qty' => 3, 'price' => 50]]
            );
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE)
            ->willReturn(0);

        $this->productMock->expects($this->once())->method('setData')->with(
            'tier_price',
            [
                ['cust_group' => 1, 'website_id' => 0, 'price_qty' => 3, 'price' => 100]
            ]
        );
        $this->repositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->service->add('product_sku', 1, 100, 3);
    }

    public function testSetThrowsExceptionIfDoesntValidate()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage(
            'Values in the attr1, attr2 attributes are invalid. Verify the values and try again.'
        );
        $group = $this->createMock(Group::class);
        $group->expects($this->once())->method('getId')->willReturn(1);
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn([]);

        $this->groupRepositoryMock->expects($this->once())->method('getById')->willReturn($group);
        $this->productMock->expects($this->once())->method('validate')->willReturn(
            ['attr1' => '', 'attr2' => '']
        );
        $this->repositoryMock->expects($this->never())->method('save');
        $this->service->add('product_sku', 1, 100, 2);
    }

    public function testSetThrowsExceptionIfCantSave()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $group = $this->createMock(Group::class);
        $group->expects($this->once())->method('getId')->willReturn(1);
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn([]);

        $this->groupRepositoryMock->expects($this->once())->method('getById')->willReturn($group);
        $this->repositoryMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->service->add('product_sku', 1, 100, 2);
    }

    public function testAddRethrowsTemporaryStateExceptionIfRecoverableErrorOccurred()
    {
        $this->expectException('Magento\Framework\Exception\TemporaryState\CouldNotSaveException');
        $group = $this->createMock(Group::class);
        $group->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn([]);
        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($group);
        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->willThrowException(new CouldNotSaveException(__('Lock wait timeout')));

        $this->service->add('product_sku', 1, 100, 2);
    }

    /**
     * @param string|int $price
     * @param string|float $qty
     * @dataProvider addDataProvider
     */
    public function testAddWithInvalidData($price, $qty)
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->service->add('product_sku', 1, $price, $qty);
    }

    /**
     * @return array
     */
    public function addDataProvider()
    {
        return [
            ['string', 10],
            [10, '10string'],
            [10, -15]
        ];
    }
}
