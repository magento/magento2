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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockItemRepository
 */
interface StockItemRepositoryInterface
{
    /**
     * Save Stock Item data
     *
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function save(\Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem);

    /**
     * Load Stock Item data by given stockId and parameters
     *
     * @param string $stockId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function get($stockId);

    /**
     * Load Stock Item data collection by given search criteria
     *
     * @param \Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria
     * @return \Magento\CatalogInventory\Api\Data\StockItemCollectionInterface
     */
    public function getList(\Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria);

    /**
     * Delete stock item
     *
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
     * @return true
     */
    public function delete(\Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem);

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById($id);
}
