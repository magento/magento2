<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

class Cancel extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * Cancel action
     * Set billing agreement status to 'Canceled'
     *
     * @return void
     */
    public function execute()
    {
        $agreement = $this->_initAgreement();
        if (!$agreement) {
            return;
        }
        if ($agreement->canCancel()) {
            try {
                $agreement->cancel();
                $this->messageManager->addNotice(
                    __('The billing agreement "%1" has been canceled.', $agreement->getReferenceId())
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError(__('We can\'t cancel the billing agreement.'));
            }
        }
        $this->_redirect('*/*/view', ['_current' => true]);
    }
}
