<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type;

/**
 * Customers defined options
 */
class Select extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\AbstractType
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/edit/options/type/select.phtml';

    /**
     * Initialise the block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Prepare the layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'add_select_row_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Add New Row'),
                'class' => 'add add-select-row',
                'id' => 'product_option_<%- data.option_id %>_add_select_row'
            ]
        );

        $this->addChild(
            'delete_select_row_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Delete Row'),
                'class' => 'delete delete-select-row icon-btn',
                'id' => 'product_option_<%- data.id %>_select_<%- data.select_id %>_delete'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Return the HTML for the add button
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_select_row_button');
    }

    /**
     * Return the HTML for the delete button
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_select_row_button');
    }

    /**
     * Return select input for price type
     *
     * @param string $extraParams
     * @return string
     */
    public function getPriceTypeSelectHtml($extraParams = '')
    {
        $this->getChildBlock(
            'option_price_type'
        )->setData(
            'id',
            'product_option_<%- data.id %>_select_<%- data.select_id %>_price_type'
        )->setName(
            'product[options][<%- data.id %>][values][<%- data.select_id %>][price_type]'
        );

        return parent::getPriceTypeSelectHtml($extraParams);
    }
}
