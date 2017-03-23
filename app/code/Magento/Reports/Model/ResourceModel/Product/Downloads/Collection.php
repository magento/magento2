<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Downloads Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Product\Downloads;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Identifier field name
     *
     * @var string
     */
    protected $_idFieldName = 'link_id';

    /**
     * Add downloads summary grouping by product
     *
     * @return $this
     */
    public function addSummary()
    {
        $connection = $this->getConnection();
        $linkExpr = $connection->getIfNullSql('l_store.title', 'l.title');

        $this->getSelect()->joinInner(
            ['d' => $this->getTable('downloadable_link_purchased_item')],
            'e.entity_id = d.product_id',
            [
                'purchases' => new \Zend_Db_Expr('SUM(d.number_of_downloads_bought)'),
                'downloads' => new \Zend_Db_Expr('SUM(d.number_of_downloads_used)')
            ]
        )->joinInner(
            ['l' => $this->getTable('downloadable_link_title')],
            'd.link_id = l.link_id',
            ['l.link_id']
        )->joinLeft(
            ['l_store' => $this->getTable('downloadable_link_title')],
            $connection->quoteInto('l.link_id = l_store.link_id AND l_store.store_id = ?', (int)$this->getStoreId()),
            ['link_title' => $linkExpr]
        )->where(
            implode(
                ' OR ',
                [
                    $connection->quoteInto('d.number_of_downloads_bought > ?', 0),
                    $connection->quoteInto('d.number_of_downloads_used > ?', 0)
                ]
            )
        )->group(
            'd.link_id'
        );
        return $this;
    }

    /**
     * Add sorting
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        if ($attribute == 'purchases' || $attribute == 'downloads' || $attribute == 'link_title') {
            $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
            parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    /**
     * Add filtering
     *
     * @param string $field
     * @param null|string $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'link_title') {
            $conditionSql = $this->_getConditionSql('l.title', $condition);
            $this->getSelect()->where($conditionSql);
        } else {
            parent::addFieldToFilter($field, $condition);
        }
        return $this;
    }
}
