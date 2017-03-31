<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Config\Backend\Email;

/**
 * Backend model for global configuration value
 * 'sales_email/general/async_sending'.
 */
class AsyncSending extends \Magento\Framework\App\Config\Value
{
    /**
     * Dispatches corresponding event after saving of configuration
     * value if it was changed.
     *
     * Dispatches next events:
     *
     * - config_data_sales_email_general_async_sending_enabled
     * - config_data_sales_email_general_async_sending_disabled
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $state = $this->getValue() ? 'enabled' : 'disabled';

            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_sales_email_general_async_sending_' . $state,
                $this->_getEventData()
            );
        }

        return $this;
    }
}
