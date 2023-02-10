<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Class for save rates.
 */
class SaveRates extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency implements HttpPostActionInterface
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
                        $value = abs(
                            (float) $this->_objectManager->get(\Magento\Framework\Locale\FormatInterface::class)
                                ->getNumber($value)
                        );
                        $data[$currencyCode][$currencyTo] = $value;
                        if ($value == 0) {
                            $this->messageManager->addWarningMessage(
                                __('Please correct the input data for "%1 => %2" rate.', $currencyCode, $currencyTo)
                            );
                        }
                    }
                }

                $this->_objectManager->create(\Magento\Directory\Model\Currency::class)->saveRates($data);
                $this->messageManager->addSuccessMessage(__('All valid rates have been saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/*/');
    }
}
