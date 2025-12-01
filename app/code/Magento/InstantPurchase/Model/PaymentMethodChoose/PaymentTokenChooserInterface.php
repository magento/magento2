<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\InstantPurchase\Model\PaymentMethodChoose;

use Magento\Customer\Model\Customer;
use Magento\Store\Model\Store;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Interface to choose one of the stored payment methods for a customer if available.
 *
 * @api
 * @since 100.2.0
 */
interface PaymentTokenChooserInterface
{
    /**
     * @param Store $store
     * @param Customer $customer
     * @return PaymentTokenInterface|null
     * @since 100.2.0
     */
    public function choose(Store $store, Customer $customer);
}
