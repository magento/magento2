<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report Sold Products collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Product\Sold;

class Collection extends \Magento\Reports\Model\Resource\Product\Collection
{
    /**
     * Set Date range to collection
     *
     * @param int $from
     * @param int $to
     * @return $this
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()->addAttributeToSelect(
            '*'
        )->addOrderedQty(
            $from,
            $to
        )->setOrder(
            'ordered_qty',
            self::SORT_ORDER_DESC
        );
        return $this;
    }

    /**
     * Set store filter to collection
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->getSelect()->where('order_items.store_id IN (?)', (array)$storeIds);
        }
        return $this;
    }

    /**
     * Add website product limitation
     *
     * @return $this
     */
    protected function _productLimitationJoinWebsite()
    {
        $filters = $this->_productLimitationFilters;
        $conditions = ['product_website.product_id=e.entity_id'];
        if (isset($filters['website_ids'])) {
            $conditions[] = $this->getConnection()->quoteInto(
                'product_website.website_id IN(?)',
                $filters['website_ids']
            );

            $subQuery = $this->getConnection()->select()->from(
                ['product_website' => $this->getTable('catalog_product_website')],
                ['product_website.product_id']
            )->where(
                join(' AND ', $conditions)
            );
            $this->getSelect()->where('e.entity_id IN( ' . $subQuery . ' )');
        }

        return $this;
    }
}
