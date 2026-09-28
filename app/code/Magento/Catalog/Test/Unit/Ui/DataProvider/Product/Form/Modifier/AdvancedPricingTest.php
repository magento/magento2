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
}
