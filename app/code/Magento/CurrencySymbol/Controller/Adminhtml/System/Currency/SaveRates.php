<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

class SaveRates extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency
{
    /**
     * Save rates action
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('rate');
        if (is_array($data)) {
            try {
                foreach ($data as $currencyCode => $rate) {
                    foreach ($rate as $currencyTo => $value) {
                        $value = abs($this->_objectManager->get(
                            \Magento\Framework\Locale\FormatInterface::class)->getNumber($value));
                        $data[$currencyCode][$currencyTo] = $value;
                        if ($value == 0) {
                            $this->messageManager->addWarning(
                                __('Please correct the input data for "%1 => %2" rate.', $currencyCode, $currencyTo)
                            );
                        }
                    }
                }

                $this->_objectManager->create(\Magento\Directory\Model\Currency::class)->saveRates($data);
                $this->messageManager->addSuccess(__('All valid rates have been saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/*/');
    }
}
