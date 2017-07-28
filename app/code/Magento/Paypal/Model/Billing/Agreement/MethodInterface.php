<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Billing\Agreement;

/**
 * Interface for payment methods that support billing agreements management
 * @since 2.0.0
 */
interface MethodInterface
{
    /**
     * Init billing agreement
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return $this
     * @since 2.0.0
     */
    public function initBillingAgreementToken(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement);

    /**
     * Retrieve billing agreement details
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return array
     * @since 2.0.0
     */
    public function getBillingAgreementTokenInfo(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement);

    /**
     * Create billing agreement
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return $this
     * @since 2.0.0
     */
    public function placeBillingAgreement(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement);

    /**
     * Update billing agreement status
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return $this
     * @since 2.0.0
     */
    public function updateBillingAgreementStatus(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement);
}
