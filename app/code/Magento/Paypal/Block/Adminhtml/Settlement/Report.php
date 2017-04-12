<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Settlement;

/**
 * Adminhtml paypal settlement reports grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Report extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Prepare grid container, add additional buttons
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Paypal';
        $this->_controller = 'adminhtml_settlement_report';
        $this->_headerText = __('PayPal Settlement Reports');
        parent::_construct();
        $this->buttonList->remove('add');
        $message = __(
            'We are connecting to the PayPal SFTP server to retrieve new reports. Are you sure you want to continue?'
        );
        if (true == $this->_authorization->isAllowed('Magento_Paypal::fetch')) {
            $this->buttonList->add(
                'fetch',
                [
                    'label' => __('Fetch Updates'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getUrl('*/*/fetch')}')",
                    'class' => 'task'
                ]
            );
        }
    }
}
