<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AdvancedPricing;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class AdvancedPricingTest
 *
 * @method AdvancedPricing getModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedPricingTest extends AbstractModifierTest
{
    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var GroupRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var GroupManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $groupManagementMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var ModuleManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var DirectoryHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var ProductResource|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productResourceMock;

    /**
     * @var Attribute|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeMock;

    /**
     * @var CustomerGroupInterface|\PHPUnit\Framework\MockObject\MockObject
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
