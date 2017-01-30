<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

use Magento\Framework\Controller\ResultFactory;

class ReturnWizard extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * Wizard return action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Paypal\Model\Billing\Agreement $agreement */
        $agreement = $this->_objectManager->create('Magento\Paypal\Model\Billing\Agreement');
        $paymentCode = $this->getRequest()->getParam('payment_method');
        $token = $this->getRequest()->getParam('token');

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($token && $paymentCode) {
            try {
                $agreement->setStoreId(
                    $this->_objectManager->get('Magento\Store\Model\StoreManager')->getStore()->getId()
                )->setToken(
                    $token
                )->setMethodCode(
                    $paymentCode
                )->setCustomerId(
                    $this->_getSession()->getCustomerId()
                )->place();

                $this->messageManager->addSuccessMessage(
                    __('The billing agreement "%1" has been created.', $agreement->getReferenceId())
                );
                return $resultRedirect->setPath('*/*/view', ['agreement' => $agreement->getId()]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    $e->getMessage()
                );
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We couldn\'t finish the billing agreement wizard.')
                );
            }

            return $resultRedirect->setPath('*/*/index');
        }
    }
}
