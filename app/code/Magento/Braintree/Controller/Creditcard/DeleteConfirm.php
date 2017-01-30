<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Creditcard;

use \Braintree_Result_Error;

class DeleteConfirm extends \Magento\Braintree\Controller\MyCreditCards
{
    /**
     * Add errors from Braintree into customer session
     *
     * @param \Braintree_Result_Error $errors
     * @return $this
     */
    protected function _addError($errors)
    {
        $messages = explode("\n", $errors->message);
        foreach ($messages as $error) {
            $this->messageManager->addError(__($error));
        }
        return $this;
    }

    /**
     * Save a new credit card action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if ($token = $this->hasToken()) {
            $result = $this->vault->deleteCard($token);
            if (!$result) {
                $this->messageManager->addError(__('There was error deleting the credit card'));
            } elseif ($result->success) {
                $this->messageManager->addSuccess(__('Credit card successfully deleted'));
            } else {
                $this->_addError($result);
            }
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('braintree/creditcard/index');
        return $resultRedirect;
    }
}
