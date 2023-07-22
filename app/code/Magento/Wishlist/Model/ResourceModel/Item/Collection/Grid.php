<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Model\ResourceModel\Item\Collection;

use Magento\Catalog\Model\Entity\AttributeFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\ConfigFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Helper\Admin;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Config;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Item as ResourceItem;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Psr\Log\LoggerInterface;

/**
 * Wishlist item collection for grid grouped by customer id
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grid extends Collection
{
    /**
     * @var Registry
     */
    protected $_registryManager;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param Admin $adminhtmlSales
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     * @param Config $wishlistConfig
     * @param Visibility $productVisibility
     * @param ResourceConnection $coreResource
     * @param ResourceItem\Option\CollectionFactory $optionCollectionFactory
     * @param CollectionFactory $productCollectionFactory
     * @param ConfigFactory $catalogConfFactory
     * @param AttributeFactory $catalogAttrFactory
     * @param ResourceItem $resource
     * @param State $appState
     * @param Registry $registry
     * @param AdapterInterface $connection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StockConfigurationInterface $stockConfiguration,
        Admin $adminhtmlSales,
        StoreManagerInterface $storeManager,
        DateTime $date,
        Config $wishlistConfig,
        Visibility $productVisibility,
        ResourceConnection $coreResource,
        ResourceItem\Option\CollectionFactory $optionCollectionFactory,
        CollectionFactory $productCollectionFactory,
        ConfigFactory $catalogConfFactory,
        AttributeFactory $catalogAttrFactory,
        ResourceItem $resource,
        State $appState,
        Registry $registry,
        AdapterInterface $connection = null
    ) {
        $this->_registryManager = $registry;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $stockConfiguration,
            $adminhtmlSales,
            $storeManager,
            $date,
            $wishlistConfig,
            $productVisibility,
            $coreResource,
            $optionCollectionFactory,
            $productCollectionFactory,
            $catalogConfFactory,
            $catalogAttrFactory,
            $resource,
            $appState,
            $connection
        );
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $customerId = $this->_registryManager->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->addDaysInWishlist()
            ->addStoreData()
            ->addCustomerIdFilter($customerId)
            ->resetSortOrder();

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _assignProducts()
    {
        /** @var ProductCollection $productCollection */
        $productCollection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect($this->_wishlistConfig->getProductAttributes())
            ->addIdFilter($this->_productIds);

        /** @var Item $item */
        foreach ($this as $item) {
            $product = $productCollection->getItemById($item->getProductId());
            if ($product) {
                $product->setCustomOptions([]);
                $item->setProduct($product);
                $item->setProductName($product->getName());
                $item->setName($product->getName());
                $item->setPrice($product->getPrice());
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == 'product_name') {
            return $this->setOrderByProductName($direction);
        } else {
            if ($field == 'days_in_wishlist') {
                $field = 'added_at';
                $direction = $direction == self::SORT_ORDER_DESC ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;
            }
            return parent::setOrder($field, $direction);
        }
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch ($field) {
            case 'product_name':
                $value = (string)$condition['like'];
                $value = trim(trim($value, "'"), "%");
                return $this->addProductNameFilter($value);
            case 'store_id':
                if (isset($condition['eq'])) {
                    return $this->addStoreFilter($condition);
                }
                break;
            case 'days_in_wishlist':
                if (!isset($condition['datetime'])) {
                    return $this->addDaysFilter($condition);
                }
                break;
            case 'qty':
                if (isset($condition['from']) || isset($condition['to'])) {
                    return $this->addQtyFilter($field, $condition);
                }
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add quantity to filter
     *
     * @param string $field
     * @param array $condition
     * @return Collection
     */
    private function addQtyFilter(string $field, array $condition)
    {
        return parent::addFieldToFilter('main_table.' . $field, $condition);
    }
}
