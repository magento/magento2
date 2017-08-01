<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

/**
 * Class \Magento\Paypal\Controller\Adminhtml\Billing\Agreement\Cancel
 *
 * @since 2.0.0
 */
class Cancel extends \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Paypal::actions_manage';

    /**
     * Cancel billing agreement action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $agreementModel = $this->_initBillingAgreement();

        if ($agreementModel && $agreementModel->canCancel()) {
            try {
                $agreementModel->cancel();
                $this->messageManager->addSuccessMessage(
                    __('You canceled the billing agreement.')
                );
                $this->_redirect('paypal/*/view', ['_current' => true]);
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    $e->getMessage()
                );
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t cancel the billing agreement.')
                );
            }
            $this->_redirect('paypal/*/view', ['_current' => true]);
        }
        return $this->_redirect('paypal/*/');
    }
}
