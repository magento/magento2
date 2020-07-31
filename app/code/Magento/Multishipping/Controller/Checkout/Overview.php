<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Controller\Checkout;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Multishipping\Controller\Checkout;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Payment\Model\Method\AbstractMethod;
use Psr\Log\LoggerInterface;

class Overview extends Checkout implements HttpPostActionInterface, HttpGetActionInterface
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
            if (!empty($payment)) {
                $payment['checks'] = [
                    AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                    AbstractMethod::CHECK_ZERO_TOTAL,
                ];
                $this->_getCheckout()->setPaymentMethod($payment);
            }
            $this->_getState()->setCompleteStep(State::STEP_BILLING);

            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/billing');
        } catch (\Exception $e) {
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
            $this->messageManager->addException($e, __('We cannot open the overview page.'));
            $this->_redirect('*/*/billing');
        }
    }
}
