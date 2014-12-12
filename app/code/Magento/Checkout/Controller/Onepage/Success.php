<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Controller\Onepage;

class Success extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Order success action
     *
     * @return void
     */
    public function execute()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator')->isValid($session)) {
            $this->_redirect('checkout/cart');
            return;
        }
        $session->clearQuote();
        //@todo: Refactor it to match CQRS
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => [$session->getLastOrderId()]]
        );
        $this->_view->renderLayout();
    }
}
