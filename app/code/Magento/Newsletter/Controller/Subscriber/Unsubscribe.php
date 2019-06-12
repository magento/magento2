<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Controller\Subscriber;

<<<<<<< HEAD
/**
 * Controller for unsubscribing customers.
 */
class Unsubscribe extends \Magento\Newsletter\Controller\Subscriber
{
    /**
     * Unsubscribe newsletter
     * @return \Magento\Backend\Model\View\Result\Redirect
=======
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Controller for unsubscribing customers.
 */
class Unsubscribe extends \Magento\Newsletter\Controller\Subscriber implements HttpGetActionInterface
{
    /**
     * Unsubscribe newsletter.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $code = (string)$this->getRequest()->getParam('code');

        if ($id && $code) {
            try {
                $this->_subscriberFactory->create()->load($id)->setCheckCode($code)->unsubscribe();
                $this->messageManager->addSuccessMessage(__('You unsubscribed.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e, __('Something went wrong while unsubscribing you.'));
            }
        }
<<<<<<< HEAD
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirect */
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->_redirect->getRedirectUrl();

=======
        /** @var \Magento\Framework\Controller\Result\Redirect $redirect */
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->_redirect->getRedirectUrl();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $redirect->setUrl($redirectUrl);
    }
}
