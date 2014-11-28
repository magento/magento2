<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model\Adminhtml\Stock;

use Magento\Framework\Api\MetadataServiceInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface as StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface as StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Api\GroupManagementInterface;

/**
 * Catalog Inventory Stock Model for adminhtml area
 */
class Item extends \Magento\CatalogInventory\Model\Stock\Item
{
    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MetadataServiceInterface $metadataService
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param GroupManagementInterface $groupManagement,
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MetadataServiceInterface $metadataService,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\StoreManagerInterface $storeManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        StockItemRepositoryInterface $stockItemRepository,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $metadataService,
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
     */
    protected function _isQtyCheckApplicable()
    {
        return true;
    }

    /**
     * Check if notification message should be added despite of backorders notification flag
     *
     * @return bool
     */
    protected function _hasDefaultNotificationMessage()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function hasAdminArea()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function getShowDefaultNotificationMessage()
    {
        return true;
    }
}
