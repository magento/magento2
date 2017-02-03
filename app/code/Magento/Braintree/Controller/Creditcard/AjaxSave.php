<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Creditcard;

use Magento\Framework\Controller\ResultFactory;

class AjaxSave extends \Magento\Braintree\Controller\MyCreditCards
{
    /**
     * Save Tax Rate via AJAX
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->vault->processNonce(
                $this->getRequest()->getParam('nonce'),
                $this->getRequest()->getParam('options'),
                $this->getRequest()->getParam('billingAddress')
            );
            $this->messageManager->addSuccess(__('Credit card successfully added'));
            $responseContent = [
                'success' => true,
                'error_message' => '',
            ];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __($e->getMessage()),
            ];
            $this->messageManager->addError(__($e->getMessage()));
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('Something went wrong while saving the card.'),
            ];
            $this->messageManager->addError(__('Something went wrong while saving the card.'));
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
