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
namespace Magento\CatalogInventory\Model\Resource\Stock\Item;

use Magento\Framework\Data\AbstractCriteria;

/**
 * Class StockItemCriteria
 */
class StockItemCriteria extends AbstractCriteria implements \Magento\CatalogInventory\Api\StockItemCriteriaInterface
{
    /**
     * @param string $mapper
     */
    public function __construct($mapper = '')
    {
        $this->mapperInterfaceName = $mapper ?: 'Magento\CatalogInventory\Model\Resource\Stock\Item\StockItemCriteriaMapper';
        $this->data['initial_condition'] = true;
    }

    /**
     * @inheritdoc
     */
    public function setStockStatus($storeId = null)
    {
        $this->data['stock_status'] = func_get_args();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setStockFilter($stock)
    {
        $this->data['stock_filter'] = $stock;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteFilter($website)
    {
        $this->data['website_filter'] = $website;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setProductsFilter($products)
    {
        $this->data['products_filter'] = $products;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setManagedFilter($isStockManagedInConfig)
    {
        $this->data['managed_filter'] = $isStockManagedInConfig;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setQtyFilter($comparisonMethod, $qty)
    {
        $this->data['qty_filter'] = [$comparisonMethod, $qty];
        return true;
    }

    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria
     * @return bool
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
        return true;
    }
}
