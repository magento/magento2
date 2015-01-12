<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class Index extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Checkout page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_objectManager->get('Magento\Checkout\Helper\Data')->canOnepageCheckout()) {
            $this->messageManager->addError(__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->_customerSession->regenerateId();
        $this->_objectManager->get('Magento\Checkout\Model\Session')->setCartWasUpdated(false);
        $currentUrl = $this->_objectManager->create('Magento\Framework\UrlInterface')
            ->getUrl(
                '*/*/*',
                ['_secure' => true]
            );
        $this->_objectManager->get('Magento\Customer\Model\Session')->setBeforeAuthUrl($currentUrl);
        $this->getOnepage()->initCheckout();
        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();
        $layout->initMessages();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Checkout'));
        $this->_view->renderLayout();
    }
}
