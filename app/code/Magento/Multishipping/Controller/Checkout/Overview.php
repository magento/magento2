<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

class Overview extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Multishipping checkout place order page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        $this->_getState()->setActiveStep(State::STEP_OVERVIEW);

        try {
            $payment = $this->getRequest()->getPost('payment', []);
            $payment['checks'] = [
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
            ];
            $this->_getCheckout()->setPaymentMethod($payment);

            $this->_getState()->setCompleteStep(State::STEP_BILLING);

            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/billing');
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addException($e, __('We cannot open the overview page.'));
            $this->_redirect('*/*/billing');
        }
    }
}
