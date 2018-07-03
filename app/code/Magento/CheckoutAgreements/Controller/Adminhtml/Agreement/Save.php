<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

class Save extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement
{
    /**
     * @return void
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();
        if ($postData) {
            $model = $this->_agreementFactory->create();
            $model->setData($postData);

            try {
                $validationResult = $model->validateData(new \Magento\Framework\DataObject($postData));
                if ($validationResult !== true) {
                    foreach ($validationResult as $message) {
                        $this->messageManager->addError($message);
                    }
                } else {
                    $model->save();
                    $this->messageManager->addSuccess(__('You saved the condition.'));
                    $this->_redirect('checkout/*/');
                    return;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving this condition.'));
            }

            $this->_session->setAgreementData($postData);
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
        }
    }
}
