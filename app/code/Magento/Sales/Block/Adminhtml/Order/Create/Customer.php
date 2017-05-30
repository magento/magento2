<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * Adminhtml sales order create block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Customer extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_customer');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please select a customer');
    }

    /**
     * Get buttons html
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        if ($this->_authorization->isAllowed('Magento_Customer::manage')) {
            $addButtonData = [
                'label' => __('Create New Customer'),
                'onclick' => 'order.setCustomerId(false)',
                'class' => 'primary',
            ];
            return $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
                ->setData($addButtonData)
                ->toHtml();
        }
        return '';
    }
}
