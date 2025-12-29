<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Adminhtml\Stock;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Adminhtml\Stock\Item;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class ItemTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Item|MockObject
     */
    protected $_model;

    /**
     * setUp
     */
    protected function setUp(): void
    {
        $resourceMock = $this->createPartialMockWithReflection(
            AbstractDb::class,
            ['getConnection', 'getIdFieldName', '_construct']
        );

        $groupManagement = $this->createMock(GroupManagementInterface::class);

        $allGroup = $this->createMock(GroupInterface::class);

        $allGroup->method('getId')->willReturn(32000);

        $groupManagement->method('getAllCustomersGroup')->willReturn($allGroup);

        $contextMock = $this->createMock(Context::class);
        $registryMock = $this->createMock(Registry::class);
        $extensionFactoryMock = $this->createMock(ExtensionAttributesFactory::class);
        $customAttributeFactoryMock = $this->createMock(AttributeValueFactory::class);
        $customerSessionMock = $this->createMock(Session::class);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $stockConfigurationMock = $this->createMock(StockConfigurationInterface::class);
        $stockRegistryMock = $this->createMock(StockRegistryInterface::class);
        $stockItemRepositoryMock = $this->createMock(StockItemRepositoryInterface::class);

        $this->_model = new Item(
            $contextMock,
            $registryMock,
            $extensionFactoryMock,
            $customAttributeFactoryMock,
            $customerSessionMock,
            $storeManagerMock,
            $stockConfigurationMock,
            $stockRegistryMock,
            $stockItemRepositoryMock,
            $groupManagement,
            $resourceMock
        );
    }

    public function testGetCustomerGroupId()
    {
        $this->_model->setCustomerGroupId(null);
        $this->assertEquals(32000, $this->_model->getCustomerGroupId());
        $this->_model->setCustomerGroupId(2);
        $this->assertEquals(2, $this->_model->getCustomerGroupId());
    }

    public function testGetIdentities()
    {
        $this->_model->setProductId(1);
        $this->assertEquals(['cat_p_1'], $this->_model->getIdentities());
    }
}
