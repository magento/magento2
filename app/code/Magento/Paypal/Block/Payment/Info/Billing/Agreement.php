<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Block\Payment\Info\Billing;

/**
 * Billing Agreement info block
 */
class Agreement extends \Magento\Payment\Block\Info
{
    /**
     * Add reference id to payment method information
     *
     * @param \Magento\Framework\Object|array|null $transport
     * @return \Magento\Framework\Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $referenceID = $info->getAdditionalInformation(
            \Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement::PAYMENT_INFO_REFERENCE_ID
        );
        $transport = new \Magento\Framework\Object([(string)__('Reference ID') => $referenceID]);
        $transport = parent::_prepareSpecificInformation($transport);

        return $transport;
    }
}
