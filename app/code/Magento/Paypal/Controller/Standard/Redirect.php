<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Standard;

class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * When a customer chooses Paypal on Checkout/Payment page
     *
     * @return void
     */
    public function execute()
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $session->setPaypalStandardQuoteId($session->getQuoteId());
        $this->_view->loadLayout(false)->renderLayout();
        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }
}
