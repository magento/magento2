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
 * @category    Magento
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Bundle Options Resource Collection
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Bundle\Model\Resource\Option;

class Collection extends \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * All item ids cache
     *
     * @var array
     */
    protected $_itemIds;

    /**
     * True when selections a
     *
     * @var bool
     */
    protected $_selectionsAppended   = false;

    /**
     * Init model and resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Bundle\Model\Option', 'Magento\Bundle\Model\Resource\Option');
    }

    /**
     * Joins values to options
     *
     * @param int $storeId
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function joinValues($storeId)
    {
        $this->getSelect()
            ->joinLeft(
                array('option_value_default' => $this->getTable('catalog_product_bundle_option_value')),
                'main_table.option_id = option_value_default.option_id and option_value_default.store_id = 0',
                array()
            )
            ->columns(array('default_title' => 'option_value_default.title'));

        $title = $this->getConnection()->getCheckSql(
            'option_value.title IS NOT NULL',
            'option_value.title',
            'option_value_default.title'
        );
        if ($storeId !== null) {
            $this->getSelect()
                ->columns(array('title' => $title))
                ->joinLeft(array('option_value' => $this->getTable('catalog_product_bundle_option_value')),
                    $this->getConnection()->quoteInto(
                        'main_table.option_id = option_value.option_id and option_value.store_id = ?',
                        $storeId
                    ),
                    array()
                );
        }
        return $this;
    }

    /**
     * Sets product id filter
     *
     * @param int $productId
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function setProductIdFilter($productId)
    {
        $this->addFieldToFilter('main_table.parent_id', $productId);
        return $this;
    }

    /**
     * Sets order by position
     *
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function setPositionOrder()
    {
        $this->getSelect()->order('main_table.position asc')
            ->order('main_table.option_id asc');
        return $this;
    }

    /**
     * Append selection to options
     * stripBefore - indicates to reload
     * appendAll - indicates do we need to filter by saleable and required custom options
     *
     * @param \Magento\Bundle\Model\Resource\Selection\Collection $selectionsCollection
     * @param bool $stripBefore
     * @param bool $appendAll
     * @return array
     */
    public function appendSelections($selectionsCollection, $stripBefore = false, $appendAll = true)
    {
        if ($stripBefore) {
            $this->_stripSelections();
        }

        if (!$this->_selectionsAppended) {
            foreach ($selectionsCollection->getItems() as $key => $_selection) {
                if ($_option = $this->getItemById($_selection->getOptionId())) {
                    if ($appendAll || ($_selection->isSalable() && !$_selection->getRequiredOptions())) {
                        $_selection->setOption($_option);
                        $_option->addSelection($_selection);
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
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    protected function _stripSelections()
    {
        foreach ($this->getItems() as $option) {
            $option->setSelections(array());
        }
        $this->_selectionsAppended = false;
        return $this;
    }

    /**
     * Sets filter by option id
     *
     * @param array|int $ids
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function setIdFilter($ids)
    {
        if (is_array($ids)) {
            $this->addFieldToFilter('main_table.option_id', array('in' => $ids));
        } else if ($ids != '') {
            $this->addFieldToFilter('main_table.option_id', $ids);
        }
        return $this;
    }

    /**
     * Reset all item ids cache
     *
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function resetAllIds()
    {
        $this->_itemIds = null;
        return $this;
    }

    /**
     * Retrive all ids for collection
     *
     * @return array
     */
    public function getAllIds()
    {
        if (is_null($this->_itemIds)) {
            $this->_itemIds = parent::getAllIds();
        }
        return $this->_itemIds;
    }
}

