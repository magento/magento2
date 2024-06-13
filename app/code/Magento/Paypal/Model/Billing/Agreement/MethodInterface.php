<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Model\Billing\Agreement;

/**
 * Interface for payment methods that support billing agreements management
 *
 * @api
 */
interface MethodInterface
{
    /**
     * Init billing agreement
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return $this
     */
    public function initBillingAgreementToken(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement);

    /**
     * Retrieve billing agreement details
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return array
     */
    public function getBillingAgreementTokenInfo(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement);

    /**
     * Create billing agreement
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return $this
     */
    public function placeBillingAgreement(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement);

    /**
     * Update billing agreement status
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return $this
     */
    public function updateBillingAgreementStatus(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement);
}
