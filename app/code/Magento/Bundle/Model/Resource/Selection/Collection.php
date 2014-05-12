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
namespace Magento\Bundle\Model\Resource\Selection;

/**
 * Bundle Selections Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Catalog\Model\Resource\Product\Collection
{
    /**
     * Selection table name
     *
     * @var string
     */
    protected $_selectionTable;

    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setRowIdFieldName('selection_id');
        $this->_selectionTable = $this->getTable('catalog_product_bundle_selection');
    }

    /**
     * Set store id for each collection item when collection was loaded
     *
     * @return $this
     */
    public function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getStoreId() && $this->_items) {
            foreach ($this->_items as $item) {
                $item->setStoreId($this->getStoreId());
            }
        }
        return $this;
    }

    /**
     * Initialize collection select
     *
     * @return $this|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->join(
            array('selection' => $this->_selectionTable),
            'selection.product_id = e.entity_id',
            array('*')
        );
    }

    /**
     * Join website scope prices to collection, override default prices
     *
     * @param int $websiteId
     * @return $this
     */
    public function joinPrices($websiteId)
    {
        $adapter = $this->getConnection();
        $priceType = $adapter->getCheckSql(
            'price.selection_price_type IS NOT NULL',
            'price.selection_price_type',
            'selection.selection_price_type'
        );
        $priceValue = $adapter->getCheckSql(
            'price.selection_price_value IS NOT NULL',
            'price.selection_price_value',
            'selection.selection_price_value'
        );
        $this->getSelect()->joinLeft(
            array('price' => $this->getTable('catalog_product_bundle_selection_price')),
            'selection.selection_id = price.selection_id AND price.website_id = ' . (int)$websiteId,
            array(
                'selection_price_type' => $priceType,
                'selection_price_value' => $priceValue,
                'price_scope' => 'price.website_id'
            )
        );
        return $this;
    }

    /**
     * Apply option ids filter to collection
     *
     * @param array $optionIds
     * @return $this
     */
    public function setOptionIdsFilter($optionIds)
    {
        if (!empty($optionIds)) {
            $this->getSelect()->where('selection.option_id IN (?)', $optionIds);
        }
        return $this;
    }

    /**
     * Apply selection ids filter to collection
     *
     * @param array $selectionIds
     * @return $this
     */
    public function setSelectionIdsFilter($selectionIds)
    {
        if (!empty($selectionIds)) {
            $this->getSelect()->where('selection.selection_id IN (?)', $selectionIds);
        }
        return $this;
    }

    /**
     * Set position order
     *
     * @return $this
     */
    public function setPositionOrder()
    {
        $this->getSelect()->order('selection.position asc')->order('selection.selection_id asc');
        return $this;
    }
}
