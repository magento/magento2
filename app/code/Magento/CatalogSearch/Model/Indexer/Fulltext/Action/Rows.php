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
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

class Rows extends \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full
{
    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @return void
     */
    public function reindex(array $entityIds = array())
    {
        // Index basic products
        $this->rebuildIndex($entityIds);
        // Index parent products
        $this->rebuildIndex($this->getProductIdsFromParents($entityIds));
    }

    /**
     * Get parents IDs of product IDs to be re-indexed
     *
     * @param int[] $entityIds
     * @return int[]
     */
    protected function getProductIdsFromParents(array $entityIds)
    {
        return $this->getWriteAdapter()->select()
            ->from($this->getTable('catalog_product_relation'), 'parent_id')
            ->distinct(true)
            ->where('child_id IN (?)', $entityIds)
            ->where('parent_id NOT IN (?)', $entityIds)
            ->query()->fetchAll(\Zend_Db::FETCH_COLUMN);
    }
}
