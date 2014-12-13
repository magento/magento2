<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $result['message'] = __('Validation failed.');
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
