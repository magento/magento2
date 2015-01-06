<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Adminhtml sales order create validation card block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Centinel\Block\Adminhtml\Validation;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Prepare validation and template parameters
     *
     * @return string
     */
    protected function _toHtml()
    {
        $payment = $this->getQuote()->getPayment();
        if ($payment) {
            $method = $payment->getMethodInstance();
            if ($method->getIsCentinelValidationEnabled() && ($centinel = $method->getCentinelValidator())) {
                $this->setFrameUrl(
                    $centinel->getValidatePaymentDataUrl()
                )->setContainerId(
                    'centinel_authenticate_iframe'
                )->setMethodCode(
                    $method->getCode()
                );
                return parent::_toHtml();
            }
        }
        return '';
    }
}
