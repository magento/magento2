<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Group\AbstractGroup;

/**
 * Adminhtml group price item renderer
 */
class Group extends AbstractGroup
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/price/group.phtml';

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     */
    protected function _sortValues($data)
    {
        usort($data, [$this, '_sortGroupPrices']);
        return $data;
    }

    /**
     * Sort group price values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortGroupPrices($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? -1 : 1;
        }
        if ($a['cust_group'] != $b['cust_group']) {
            return $this->getCustomerGroups($a['cust_group']) < $this->getCustomerGroups($b['cust_group']) ? -1 : 1;
        }
        return 0;
    }

    /**
     * Prepare global layout
     *
     * Add "Add Group Price" button to layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Add Group Price'),
                'onclick' => 'return groupPriceControl.addItem()',
                'class' => 'add',
            ]
        );
        $button->setName('add_group_price_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }
}
