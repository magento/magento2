<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Quote\Model;

use Magento\Quote\Model\Quote as QuoteEntity;

class QuoteValidator
{
    /**
     * Validate quote before submit
     *
     * @param Quote $quote
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function validateBeforeSubmit(QuoteEntity $quote)
    {
        if (!$quote->isVirtual()) {
            if ($quote->getShippingAddress()->validate() !== true) {
                throw new \Magento\Framework\Model\Exception(__(
                    'Please check the shipping address information. %1',
                        implode(' ', $quote->getShippingAddress()->validate())
                    )
                );
            }
            $method = $quote->getShippingAddress()->getShippingMethod();
            $rate = $quote->getShippingAddress()->getShippingRateByCode($method);
            if (!$quote->isVirtual() && (!$method || !$rate)) {
                throw new \Magento\Framework\Model\Exception(__('Please specify a shipping method.'));
            }
        }
        if ($quote->getBillingAddress()->validate() !== true) {
            throw new \Magento\Framework\Model\Exception(__(
                    'Please check the billing address information. %1',
                    implode(' ', $quote->getBillingAddress()->validate())
                )
            );
        }
        if (!$quote->getPayment()->getMethod()) {
            throw new \Magento\Framework\Model\Exception(__('Please select a valid payment method.'));
        }
        return $this;
    }
}
