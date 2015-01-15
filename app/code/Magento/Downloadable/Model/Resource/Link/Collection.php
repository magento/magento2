<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Resource\Link;

/**
 * Downloadable links resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Downloadable\Model\Link', 'Magento\Downloadable\Model\Resource\Link');
    }

    /**
     * Method for product filter
     *
     * @param \Magento\Catalog\Model\Product|array|integer|null $product
     * @return $this
     */
    public function addProductToFilter($product)
    {
        if (empty($product)) {
            $this->addFieldToFilter('product_id', '');
        } elseif ($product instanceof \Magento\Catalog\Model\Product) {
            $this->addFieldToFilter('product_id', $product->getId());
        } else {
            $this->addFieldToFilter('product_id', ['in' => $product]);
        }

        return $this;
    }

    /**
     * Retrieve title for for current store
     *
     * @param int $storeId
     * @return $this
     */
    public function addTitleToResult($storeId = 0)
    {
        $ifNullDefaultTitle = $this->getConnection()->getIfNullSql('st.title', 'd.title');
        $this->getSelect()->joinLeft(
            ['d' => $this->getTable('downloadable_link_title')],
            'd.link_id=main_table.link_id AND d.store_id = 0',
            ['default_title' => 'title']
        )->joinLeft(
            ['st' => $this->getTable('downloadable_link_title')],
            'st.link_id=main_table.link_id AND st.store_id = ' . (int)$storeId,
            ['store_title' => 'title', 'title' => $ifNullDefaultTitle]
        )->order(
            'main_table.sort_order ASC'
        )->order(
            'title ASC'
        );

        return $this;
    }

    /**
     * Retrieve price for for current website
     *
     * @param int $websiteId
     * @return $this
     */
    public function addPriceToResult($websiteId)
    {
        $ifNullDefaultPrice = $this->getConnection()->getIfNullSql('stp.price', 'dp.price');
        $this->getSelect()->joinLeft(
            ['dp' => $this->getTable('downloadable_link_price')],
            'dp.link_id=main_table.link_id AND dp.website_id = 0',
            ['default_price' => 'price']
        )->joinLeft(
            ['stp' => $this->getTable('downloadable_link_price')],
            'stp.link_id=main_table.link_id AND stp.website_id = ' . (int)$websiteId,
            ['website_price' => 'price', 'price' => $ifNullDefaultPrice]
        );

        return $this;
    }
}
