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
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog advanced search model
 *
 * @method Mage_CatalogSearch_Model_Resource_Fulltext _getResource()
 * @method Mage_CatalogSearch_Model_Resource_Fulltext getResource()
 * @method int getProductId()
 * @method Mage_CatalogSearch_Model_Fulltext setProductId(int $value)
 * @method int getStoreId()
 * @method Mage_CatalogSearch_Model_Fulltext setStoreId(int $value)
 * @method string getDataIndex()
 * @method Mage_CatalogSearch_Model_Fulltext setDataIndex(string $value)
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogSearch_Model_Fulltext extends Mage_Core_Model_Abstract
{
    const SEARCH_TYPE_LIKE              = 1;
    const SEARCH_TYPE_FULLTEXT          = 2;
    const SEARCH_TYPE_COMBINE           = 3;
    const XML_PATH_CATALOG_SEARCH_TYPE  = 'catalog/search/search_type';

    protected function _construct()
    {
        $this->_init('Mage_CatalogSearch_Model_Resource_Fulltext');
    }

    /**
     * Regenerate all Stores index
     *
     * Examples:
     * (null, null) => Regenerate index for all stores
     * (1, null)    => Regenerate index for store Id=1
     * (1, 2)       => Regenerate index for product Id=2 and its store view Id=1
     * (null, 2)    => Regenerate index for all store views of product Id=2
     *
     * @param int|null $storeId Store View Id
     * @param int|array|null $productIds Product Entity Id
     *
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    public function rebuildIndex($storeId = null, $productIds = null)
    {
        Mage::dispatchEvent('catalogsearch_index_process_start', array(
            'store_id'      => $storeId,
            'product_ids'   => $productIds
        ));

        $this->getResource()->rebuildIndex($storeId, $productIds);

        Mage::dispatchEvent('catalogsearch_index_process_complete', array());

        return $this;
    }

    /**
     * Delete index data
     *
     * Examples:
     * (null, null) => Clean index of all stores
     * (1, null)    => Clean index of store Id=1
     * (1, 2)       => Clean index of product Id=2 and its store view Id=1
     * (null, 2)    => Clean index of all store views of product Id=2
     *
     * @param int $storeId Store View Id
     * @param int $productId Product Entity Id
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    public function cleanIndex($storeId = null, $productId = null)
    {
        $this->getResource()->cleanIndex($storeId, $productId);
        return $this;
    }

    /**
     * Reset search results cache
     *
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    public function resetSearchResults()
    {
        $this->getResource()->resetSearchResults();
        return $this;
    }

    /**
     * Prepare results for query
     *
     * @param Mage_CatalogSearch_Model_Query $query
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    public function prepareResult($query = null)
    {
        if (!$query instanceof Mage_CatalogSearch_Model_Query) {
            $query = Mage::helper('Mage_CatalogSearch_Helper_Data')->getQuery();
        }
        $queryText = Mage::helper('Mage_CatalogSearch_Helper_Data')->getQueryText();
        if ($query->getSynonymFor()) {
            $queryText = $query->getSynonymFor();
        }
        $this->getResource()->prepareResult($this, $queryText, $query);
        return $this;
    }

    /**
     * Retrieve search type
     *
     * @param int $storeId
     * @return int
     */
    public function getSearchType($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CATALOG_SEARCH_TYPE, $storeId);
    }





    // Deprecated methods

    /**
     * Set whether table changes are allowed
     *
     * @deprecated after 1.6.1.0
     *
     * @param bool $value
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    public function setAllowTableChanges($value = true)
    {
        $this->_allowTableChanges = $value;
        return $this;
    }

    /**
     * Update category products indexes
     *
     * @deprecated after 1.6.2.0
     *
     * @param array $productIds
     * @param array $categoryIds
     *
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    public function updateCategoryIndex($productIds, $categoryIds)
    {
        $this->getResource()->updateCategoryIndex($productIds, $categoryIds);
        return $this;
    }
}
