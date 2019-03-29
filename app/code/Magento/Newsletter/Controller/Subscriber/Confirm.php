<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Controller\Subscriber;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Confirm subscription controller.
 */
class Confirm extends \Magento\Newsletter\Controller\Subscriber implements HttpGetActionInterface
{
    /**
     * Subscription confirm action.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $code = (string)$this->getRequest()->getParam('code');

        if ($id && $code) {
            /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
            $subscriber = $this->_subscriberFactory->create()->load($id);

            if ($subscriber->getId() && $subscriber->getCode()) {
                if ($subscriber->confirm($code)) {
                    $this->messageManager->addSuccessMessage(__('Your subscription has been confirmed.'));
                } else {
                    $this->messageManager->addErrorMessage(__('This is an invalid subscription confirmation code.'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('This is an invalid subscription ID.'));
            }
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $redirect */
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->_storeManager->getStore()->getBaseUrl();
        return $redirect->setUrl($redirectUrl);
    }
}
