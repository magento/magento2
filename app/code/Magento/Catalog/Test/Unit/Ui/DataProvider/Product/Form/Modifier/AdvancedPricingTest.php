<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method AdvancedPricing getModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedPricingTest extends AbstractModifierTestCase
{
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
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->groupManagementMock = $this->getMockBuilder(GroupManagementInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleManagerMock = $this->getMockBuilder(ModuleManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryHelperMock = $this->getMockBuilder(DirectoryHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productResourceMock = $this->getMockBuilder(ProductResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerGroupMock = $this->getMockBuilder(CustomerGroupInterface::class)
            ->getMockForAbstractClass();

        $this->groupManagementMock->expects($this->any())
            ->method('getAllCustomersGroup')
            ->willReturn($this->customerGroupMock);
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
