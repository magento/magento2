<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

class StartWizard extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * Wizard start action
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        $agreement = $this->_objectManager->create(\Magento\Paypal\Model\Billing\Agreement::class);
        $paymentCode = $this->getRequest()->getParam('payment_method');
        if ($paymentCode) {
            try {
                $agreement->setStoreId(
                    $this->_objectManager->get(\Magento\Store\Model\StoreManager::class)->getStore()->getId()
                )->setMethodCode(
                    $paymentCode
                )->setReturnUrl(
                    $this->_objectManager->create(
                        \Magento\Framework\UrlInterface::class
                    )->getUrl('*/*/returnWizard', ['payment_method' => $paymentCode])
                )->setCancelUrl(
                    $this->_objectManager->create(\Magento\Framework\UrlInterface::class)
                        ->getUrl('*/*/cancelWizard', ['payment_method' => $paymentCode])
                );

                return $this->getResponse()->setRedirect($agreement->initToken());
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t start the billing agreement wizard.')
                );
            }
        }
        $this->_redirect('*/*/');
    }
}
