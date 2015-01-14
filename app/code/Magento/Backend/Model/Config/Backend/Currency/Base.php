<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend Directory currency backend model
 * Allows dispatching before and after events for each controller action
 */
namespace Magento\Backend\Model\Config\Backend\Currency;

class Base extends AbstractCurrency
{
    /**
     * Check base currency is available in installed currencies
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function afterSave()
    {
        if (!in_array($this->getValue(), $this->_getInstalledCurrencies())) {
            throw new \Magento\Framework\Model\Exception(__('Sorry, we haven\'t installed the base currency you selected.'));
        }
        return $this;
    }
}
