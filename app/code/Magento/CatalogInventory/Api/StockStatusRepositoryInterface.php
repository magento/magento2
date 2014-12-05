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
 * Interface StockStatusRepositoryInterface
 */
interface StockStatusRepositoryInterface
{
    /**
     * Save StockStatus data
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function save(\Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus);

    /**
     * Load StockStatus data by given stockStatusId and parameters
     *
     * @param string $stockStatusId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function get($stockStatusId);

    /**
     * Load Stock Status data collection by given search criteria
     *
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $searchCriteria
     * @return \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface
     */
    public function getList(StockStatusCriteriaInterface $searchCriteria);

    /**
     * Delete StockStatus entity
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus
     * @return bool
     */
    public function delete(\Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus);
}
