<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Option;

/**
 * Bundle Options Resource Collection
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * All item ids cache
     *
     * @var array
     * @since 2.0.0
     */
    protected $_itemIds;

    /**
     * True when selections appended
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_selectionsAppended = false;

    /**
     * Init model and resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Bundle\Model\Option::class, \Magento\Bundle\Model\ResourceModel\Option::class);
    }

    /**
     * Joins values to options
     *
     * @param int $storeId
     * @return $this
     * @since 2.0.0
     */
    public function joinValues($storeId)
    {
        $this->getSelect()->joinLeft(
            ['option_value_default' => $this->getTable('catalog_product_bundle_option_value')],
            implode(
                ' AND ',
                [
                    'main_table.option_id = option_value_default.option_id',
                    'main_table.parent_id = option_value_default.parent_product_id',
                    'option_value_default.store_id = 0'
                ]
            ),
            []
        )->columns(
            ['default_title' => 'option_value_default.title']
        );

        $title = $this->getConnection()->getCheckSql(
            'option_value.title IS NOT NULL',
            'option_value.title',
            'option_value_default.title'
        );
        if ($storeId !== null) {
            $this->getSelect()->columns(
                ['title' => $title]
            )->joinLeft(
                ['option_value' => $this->getTable('catalog_product_bundle_option_value')],
                $this->getConnection()->quoteInto(
                    implode(
                        ' AND ',
                        [
                            'main_table.option_id = option_value.option_id',
                            'main_table.parent_id = option_value.parent_product_id',
                            'option_value.store_id = ?'
                        ]
                    ),
                    $storeId
                ),
                []
            );
        }
        return $this;
    }

    /**
     * Sets product id filter
     *
     * @param int $productId
     * @return $this
     * @since 2.0.0
     */
    public function setProductIdFilter($productId)
    {
        $productTable = $this->getTable('catalog_product_entity');
        $linkField = $this->getConnection()->getAutoIncrementField($productTable);
        $this->getSelect()->join(
            ['cpe' => $productTable],
            'cpe.'.$linkField.' = main_table.parent_id',
            []
        )->where(
            "cpe.entity_id = ?",
            $productId
        );

        return $this;
    }

    /**
     * Set product link filter
     *
     * @param int $productLinkFieldValue
     *
     * @return $this
     * @since 2.1.0
     */
    public function setProductLinkFilter($productLinkFieldValue)
    {
        $this->getSelect()->where(
            'main_table.parent_id = ?',
            $productLinkFieldValue
        );
        return $this;
    }

    /**
     * Sets order by position
     *
     * @return $this
     * @since 2.0.0
     */
    public function setPositionOrder()
    {
        $this->getSelect()->order('main_table.position asc')->order('main_table.option_id asc');
        return $this;
    }

    /**
     * Append selection to options
     * stripBefore - indicates to reload
     * appendAll - indicates do we need to filter by saleable and required custom options
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $stripBefore
     * @param bool $appendAll
     * @return \Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    public function appendSelections($selectionsCollection, $stripBefore = false, $appendAll = true)
    {
        if ($stripBefore) {
            $this->_stripSelections();
        }

        if (!$this->_selectionsAppended) {
            foreach ($selectionsCollection->getItems() as $key => $selection) {
                $option = $this->getItemById($selection->getOptionId());
                if ($option) {
                    if ($appendAll || $selection->isSalable() && !$selection->getRequiredOptions()) {
                        $selection->setOption($option);
                        $option->addSelection($selection);
                    } else {
                        $selectionsCollection->removeItemByKey($key);
                    }
                }
            }
            $this->_selectionsAppended = true;
        }

        return $this->getItems();
    }

    /**
     * Removes appended selections before
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _stripSelections()
    {
        foreach ($this->getItems() as $option) {
            $option->setSelections([]);
        }
        $this->_selectionsAppended = false;
        return $this;
    }

    /**
     * Sets filter by option id
     *
     * @param array|int $ids
     * @return $this
     * @since 2.0.0
     */
    public function setIdFilter($ids)
    {
        if (is_array($ids)) {
            $this->addFieldToFilter('main_table.option_id', ['in' => $ids]);
        } elseif ($ids != '') {
            $this->addFieldToFilter('main_table.option_id', $ids);
        }
        return $this;
    }

    /**
     * Reset all item ids cache
     *
     * @return $this
     * @since 2.0.0
     */
    public function resetAllIds()
    {
        $this->_itemIds = null;
        return $this;
    }

    /**
     * Retrieve all ids for collection
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllIds()
    {
        if ($this->_itemIds === null) {
            $this->_itemIds = parent::getAllIds();
        }
        return $this->_itemIds;
    }
}
