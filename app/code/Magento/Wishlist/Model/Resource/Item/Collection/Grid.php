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
 * @category    Magento
 * @package     Magento_Wishlist
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Wishlist item collection grouped by customer id
 */
namespace Magento\Wishlist\Model\Resource\Item\Collection;

class Grid extends \Magento\Wishlist\Model\Resource\Item\Collection
{
    /**
     * @var \Magento\Core\Model\Registry
     */
    protected $_registryManager;

    /**
     * @param \Magento\CatalogInventory\Helper\Data $catalogInventoryData
     * @param \Magento\Adminhtml\Helper\Sales $adminhtmlSales
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Date $date
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Core\Model\Resource $coreResource
     * @param \Magento\Wishlist\Model\Resource\Item\Option\CollectionFactory $optionCollFactory
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollFactory
     * @param \Magento\Catalog\Model\Resource\ConfigFactory $catalogConfFactory
     * @param \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Wishlist\Model\Resource\Item $resource
     */
    public function __construct(
        \Magento\CatalogInventory\Helper\Data $catalogInventoryData,
        \Magento\Adminhtml\Helper\Sales $adminhtmlSales,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Date $date,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Core\Model\Resource $coreResource,
        \Magento\Wishlist\Model\Resource\Item\Option\CollectionFactory $optionCollFactory,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollFactory,
        \Magento\Catalog\Model\Resource\ConfigFactory $catalogConfFactory,
        \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory,
        \Magento\Core\Model\Registry $registry,
        \Magento\Wishlist\Model\Resource\Item $resource
    ) {
        $this->_registryManager = $registry;
        parent::__construct($catalogInventoryData, $adminhtmlSales, $eventManager, $logger, $fetchStrategy,
            $entityFactory, $storeManager, $date, $wishlistConfig, $productVisibility, $coreResource,
            $optionCollFactory, $productCollFactory, $catalogConfFactory, $catalogAttrFactory, $resource);
    }

    /**
     * Initialize db select
     *
     * @return \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addCustomerIdFilter($this->_registryManager->registry('current_customer')->getId())
        ->resetSortOrder()
        ->addDaysInWishlist()
        ->addStoreData();
        return $this;
    }

    /**
     * Add select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  \Magento\Data\Collection\Db
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
     * Add field filter to collection
     *
     * @see self::_getConditionSql for $condition
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return \Magento\Data\Collection\Db
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch ($field) {
            case 'product_name':
                $value = (string) $condition['like'];
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
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
