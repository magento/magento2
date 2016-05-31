<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\PaymentException;

class SaveOrder extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Create order action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        $result = new DataObject();
        try {
            $agreementsValidator = $this->_objectManager->get('Magento\CheckoutAgreements\Model\AgreementsValidator');
            if (!$agreementsValidator->isValid(array_keys($this->getRequest()->getPost('agreement', [])))) {
                $result->setData('success', false);
                $result->setData('error', true);
                $result->setData(
                    'error_messages',
                    __('Please agree to all the terms and conditions before placing the order.')
                );
                return $this->resultJsonFactory->create()->setData($result->getData());
            }

            $data = $this->getRequest()->getPost('payment', []);
            if ($data) {
                $data['checks'] = [
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
                ];
                $this->getOnepage()->getQuote()->getPayment()->setQuote($this->getOnepage()->getQuote());
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result->setData('success', true);
            $result->setData('error', false);
        } catch (PaymentException $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result->setData('error_messages', $message);
            }
            $result->setData('goto_section', 'payment');
            $result->setData(
                'update_section',
                [
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                ]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_objectManager->get('Magento\Checkout\Helper\Data')
                ->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result->setData(
                'success',
                false
            );
            $result->setData('error', true);
            $result->setData('error_messages', $e->getMessage());
            $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
            if ($gotoSection) {
                $result->setData('goto_section', $gotoSection);
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

            $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result->setData(
                        'update_section',
                        [
                            'name' => $updateSection,
                            'html' => $this->{$updateSectionFunction}(),
                        ]
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_objectManager->get('Magento\Checkout\Helper\Data')
                ->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result->setData('success', false);
            $result->setData('error', true);
            $result->setData(
                'error_messages',
                __('Something went wrong while processing your order. Please try again later.')
            );
        }
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result->setData('redirect', $redirectUrl);
        }

        $this->_eventManager->dispatch(
            'checkout_controller_onepage_saveOrder',
            [
                'result' => $result,
                'action' => $this
            ]
        );

        return $this->resultJsonFactory->create()->setData($result->getData());
    }
}
