<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Adminhtml\Stock;

use Magento\CatalogInventory\Api\StockConfigurationInterface as StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface as StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Catalog\Model\Product;

/**
 * Catalog Inventory Stock Model for adminhtml area
 * @method \Magento\CatalogInventory\Api\Data\StockItemExtensionInterface getExtensionAttributes()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 2.0.0
 */
class Item extends \Magento\CatalogInventory\Model\Stock\Item implements IdentityInterface
{
    /**
     * @var GroupManagementInterface
     * @since 2.0.0
     */
    protected $groupManagement;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        StockItemRepositoryInterface $stockItemRepository,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $customerSession,
            $storeManager,
            $stockConfiguration,
            $stockRegistry,
            $stockItemRepository,
            $resource,
            $resourceCollection,
            $data
        );

        $this->groupManagement = $groupManagement;
    }

    /**
     * Getter for customer group id, return default group if not set
     *
     * @return int
     * @since 2.0.0
     */
    public function getCustomerGroupId()
    {
        if ($this->customerGroupId === null) {
            return $this->groupManagement->getAllCustomersGroup()->getId();
        }
        return parent::getCustomerGroupId();
    }

    /**
     * Check if qty check can be skipped. Skip checking in adminhtml area
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isQtyCheckApplicable()
    {
        return true;
    }

    /**
     * Check if notification message should be added despite of backorders notification flag
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _hasDefaultNotificationMessage()
    {
        return true;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function hasAdminArea()
    {
        return true;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getShowDefaultNotificationMessage()
    {
        return true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getIdentities()
    {
        $tags = [];
        if ($this->getProductId()) {
            $tags[] = Product::CACHE_TAG . '_' . $this->getProductId();
        }

        return $tags;
    }
}
