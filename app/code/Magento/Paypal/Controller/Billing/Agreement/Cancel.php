<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

/**
 * Class \Magento\Paypal\Controller\Billing\Agreement\Cancel
 *
 * @since 2.0.0
 */
class Cancel extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * Cancel action
     * Set billing agreement status to 'Canceled'
     *
     * @return void
     * @since 2.0.0
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
                $this->messageManager->addNoticeMessage(
                    __('The billing agreement "%1" has been canceled.', $agreement->getReferenceId())
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t cancel the billing agreement.'));
            }
        }
        $this->_redirect('*/*/view', ['_current' => true]);
    }
}
