<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Creditcard;

use Magento\Framework\Controller\ResultFactory;

class Generate extends \Magento\Braintree\Controller\MyCreditCards
{
    /**
     * @var string $errorMessage
     */
    protected $errorMessage = 'Something went wrong while processing.';

    /**
     * Save Tax Rate via AJAX
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            if ($this->hasToken()) {
                $nonce = $this->vault->generatePaymentMethodToken($this->hasToken());
                $responseContent = [
                    'success' => true,
                    'nonce' => $nonce,
                    'error_message' => '',
                ];
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__($this->errorMessage));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __($e->getMessage()),
            ];
            $this->messageManager->addError(__($e->getMessage()));
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __($this->errorMessage),
            ];
            $this->messageManager->addError(__($e->getMessage()));
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
