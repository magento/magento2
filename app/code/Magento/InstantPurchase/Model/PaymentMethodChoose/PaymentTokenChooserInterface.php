<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\PaymentMethodChoose;

use Magento\Customer\Model\Customer;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Interface to choose one of the stored payment methods for a customer if available.
 *
 * @api
 */
interface PaymentTokenChooserInterface
{
    /**
     * @param StoreInterface $store
     * @param Customer $customer
     * @return PaymentTokenInterface|null
     */
    public function choose(StoreInterface $store, Customer $customer);
}
