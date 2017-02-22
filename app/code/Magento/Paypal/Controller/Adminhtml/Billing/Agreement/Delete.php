<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

class Delete extends \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
{
    /**
     * Delete billing agreement action
     *
     * @return void
     */
    public function execute()
    {
        $agreementModel = $this->_initBillingAgreement();

        if ($agreementModel) {
            try {
                $agreementModel->delete();
                $this->messageManager->addSuccessMessage(
                    __('You deleted the billing agreement.')
                );
                $this->_redirect('paypal/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    $e->getMessage()
                );
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t delete the billing agreement.')
                );
            }
            $this->_redirect('paypal/*/view', ['_current' => true]);
        }
        $this->_redirect('paypal/*/');
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Paypal::actions_manage');
    }
}
