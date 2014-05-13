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
namespace Magento\Downloadable\Model\Resource\Sample;

/**
 * Downloadable samples resource collection
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
        $this->_init('Magento\Downloadable\Model\Sample', 'Magento\Downloadable\Model\Resource\Sample');
    }

    /**
     * Method for product filter
     *
     * @param \Magento\Catalog\Model\Product|array|int|null $product
     * @return $this
     */
    public function addProductToFilter($product)
    {
        if (empty($product)) {
            $this->addFieldToFilter('product_id', '');
        } elseif (is_array($product)) {
            $this->addFieldToFilter('product_id', array('in' => $product));
        } else {
            $this->addFieldToFilter('product_id', $product);
        }

        return $this;
    }

    /**
     * Add title column to select
     *
     * @param int $storeId
     * @return $this
     */
    public function addTitleToResult($storeId = 0)
    {
        $ifNullDefaultTitle = $this->getConnection()->getIfNullSql('st.title', 'd.title');
        $this->getSelect()->joinLeft(
            array('d' => $this->getTable('downloadable_sample_title')),
            'd.sample_id=main_table.sample_id AND d.store_id = 0',
            array('default_title' => 'title')
        )->joinLeft(
            array('st' => $this->getTable('downloadable_sample_title')),
            'st.sample_id=main_table.sample_id AND st.store_id = ' . (int)$storeId,
            array('store_title' => 'title', 'title' => $ifNullDefaultTitle)
        )->order(
            'main_table.sort_order ASC'
        )->order(
            'title ASC'
        );

        return $this;
    }
}
