<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price;

/**
 * Adminhtml tier price item renderer
 * @since 2.0.0
 */
class Tier extends Group\AbstractGroup
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'catalog/product/edit/price/tier.phtml';

    /**
     * Retrieve list of initial customer groups
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getInitialCustomerGroups()
    {
        return [$this->_groupManagement->getAllCustomersGroup()->getId() => __('ALL GROUPS')];
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    protected function _sortValues($data)
    {
        usort($data, [$this, '_sortTierPrices']);
        return $data;
    }

    /**
     * Sort tier price values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function _sortTierPrices($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? -1 : 1;
        }
        if ($a['cust_group'] != $b['cust_group']) {
            return $this->getCustomerGroups($a['cust_group']) < $this->getCustomerGroups($b['cust_group']) ? -1 : 1;
        }
        if ($a['price_qty'] != $b['price_qty']) {
            return $a['price_qty'] < $b['price_qty'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            ['label' => __('Add Price'), 'onclick' => 'return tierPriceControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_tier_price_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }
}
