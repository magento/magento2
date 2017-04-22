<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\Quote\Model\Quote\Item;

/**
 * Adminhtml sales order create items block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Items extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Contains button descriptions to be shown at the top of accordion
     *
     * @var array
     */
    protected $_buttons = [];

    /**
     * Define block ID
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_items');
    }

    /**
     * Accordion header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Items Ordered');
    }

    /**
     * Returns all visible items
     *
     * @return Item[]
     */
    public function getItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Add button to the items header
     *
     * @param array $args
     * @return void
     */
    public function addButton($args)
    {
        $this->_buttons[] = $args;
    }

    /**
     * Render buttons and return HTML code
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        $html = '';
        // Make buttons to be rendered in opposite order of addition. This makes "Add products" the last one.
        $this->_buttons = array_reverse($this->_buttons);
        foreach ($this->_buttons as $buttonData) {
            $html .= $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                $buttonData
            )->toHtml();
        }

        return $html;
    }

    /**
     * Return HTML code of the block
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getStoreId()) {
            return parent::_toHtml();
        }
        return '';
    }
}
