<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Centinel\Controller\Adminhtml\Centinel\Index;

class ValidatePaymentData extends \Magento\Centinel\Controller\Adminhtml\Centinel\Index
{
    /**
     * Process validate payment data action
     *
     * @return void
     */
    public function execute()
    {
        $result = [];
        try {
            $paymentData = $this->getRequest()->getParam('payment');
            $validator = $this->_getValidator();
            if (!$validator) {
                throw new \Exception('This payment method does not have centinel validation.');
            }
            $validator->reset();
            $this->_getPayment()->importData($paymentData);
            $result['authenticationUrl'] = $validator->getAuthenticationStartUrl();
        } catch (\Magento\Framework\Model\Exception $e) {
            $result['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $result['message'] = __('Validation failed.');
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
