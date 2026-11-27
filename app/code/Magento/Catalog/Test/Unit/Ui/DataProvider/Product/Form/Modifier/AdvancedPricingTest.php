<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AdvancedPricing;
use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method AdvancedPricing getModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedPricingTest extends AbstractModifierTestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var GroupManagementInterface|MockObject
     */
    protected $groupManagementMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var ModuleManager|MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var DirectoryHelper|MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var ProductResource|MockObject
     */
    protected $productResourceMock;

    /**
     * @var Attribute|MockObject
     */
    protected $attributeMock;

    /**
     * @var CustomerGroupInterface|MockObject
     */
    protected $customerGroupMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = new ObjectManager($this);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->groupRepositoryMock = $this->createMock(GroupRepositoryInterface::class);
        $this->groupManagementMock = $this->createMock(GroupManagementInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->moduleManagerMock = $this->createMock(ModuleManager::class);
        $this->directoryHelperMock = $this->createMock(DirectoryHelper::class);
        $this->productResourceMock = $this->createMock(ProductResource::class);
        $this->attributeMock = $this->createMock(Attribute::class);
        $this->customerGroupMock = $this->createMock(CustomerGroupInterface::class);

        $this->groupManagementMock->method('getAllCustomersGroup')->willReturn($this->customerGroupMock);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            AdvancedPricing::class,
            [
                'locator' => $this->locatorMock,
                'storeManager' => $this->storeManagerMock,
                'groupRepository' => $this->groupRepositoryMock,
                'groupManagement' => $this->groupManagementMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'moduleManager' => $this->moduleManagerMock,
                'directoryHelper' => $this->directoryHelperMock
            ]
        );
    }

    public function testModifyMeta()
    {
        $this->assertSame(['data_key' => 'data_value'], $this->getModel()->modifyMeta(['data_key' => 'data_value']));
    }

    public function testModifyData()
    {
        $this->assertArrayHasKey('advanced-pricing', $this->getModel()->modifyData(['advanced-pricing' => []]));
    }

    /**
     * The form leaves out tier prices of websites the product was removed from (#38099)
     *
     * The website field has no option for such a website, so the form would show and save the row as All Websites.
     *
     * @param bool $isScopeGlobal
     * @param bool $expectFiltered
     * @return void
     */
    #[DataProvider('tierPriceScopeDataProvider')]
    public function testModifyDataLeavesOutTierPricesOfUnassignedWebsites(bool $isScopeGlobal, bool $expectFiltered)
    {
        $this->productMock->setId(5);
        $this->productMock->setWebsiteIds([1]);
        $this->productMock->method('getResource')->willReturn($this->productResourceMock);
        $this->productResourceMock->method('getAttribute')->willReturn($this->attributeMock);
        $this->attributeMock->method('isScopeGlobal')->willReturn($isScopeGlobal);
        $model = $this->objectManager->getObject(
            AdvancedPricing::class,
            ['locator' => $this->locatorMock, 'arrayManager' => new ArrayManager()]
        );
        $allWebsites = ['website_id' => '0', 'cust_group' => '0', 'price_qty' => 2, 'price' => 30];
        $assigned = ['website_id' => '1', 'cust_group' => '0', 'price_qty' => 1, 'price' => 31];
        $unassigned = ['website_id' => '9', 'cust_group' => '0', 'price_qty' => 1, 'price' => 33];
        $noWebsite = ['cust_group' => '0', 'price_qty' => 3, 'price' => 28];

        $rows = [$allWebsites, $unassigned, $assigned, $noWebsite];

        $data = $model->modifyData([5 => ['product' => ['tier_price' => $rows]]]);

        $this->assertSame(
            $expectFiltered ? [$allWebsites, $assigned, $noWebsite] : $rows,
            $data[5]['product']['tier_price']
        );
        $withoutTierPrices = [5 => ['product' => ['name' => 'x']]];
        $this->assertSame($withoutTierPrices, $model->modifyData($withoutTierPrices));
    }

    /**
     * @return array
     */
    public static function tierPriceScopeDataProvider(): array
    {
        return [
            'website price scope: unassigned website left out' => [false, true],
            'global price scope: rows untouched' => [true, false],
        ];
    }
}
