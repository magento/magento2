<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payment\Info\Billing;

/**
 * Billing Agreement info block
 * @since 2.0.0
 */
class Agreement extends \Magento\Payment\Block\Info
{
    /**
     * Add reference id to payment method information
     *
     * @param \Magento\Framework\DataObject|array|null $transport
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
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
        $transport = new \Magento\Framework\DataObject([(string)__('Reference ID') => $referenceID]);
        $transport = parent::_prepareSpecificInformation($transport);

        return $transport;
    }
}
