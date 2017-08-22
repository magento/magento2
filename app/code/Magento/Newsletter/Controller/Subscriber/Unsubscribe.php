<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Subscriber;

/**
 * Class \Magento\Newsletter\Controller\Subscriber\Unsubscribe
 *
 */
class Unsubscribe extends \Magento\Newsletter\Controller\Subscriber
{
    /**
     * Unsubscribe newsletter
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $code = (string)$this->getRequest()->getParam('code');

        if ($id && $code) {
            try {
                $this->_subscriberFactory->create()->load($id)->setCheckCode($code)->unsubscribe();
                $this->messageManager->addSuccess(__('You unsubscribed.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addException($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while unsubscribing you.'));
            }
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
