<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Model\Address;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Create instances of instant purchase option.
 *
 * @api
 * @since 100.2.0
 */
class InstantPurchaseOptionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * InstantPurchaseOptionFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates new instance.
     *
     * @param PaymentTokenInterface|null $paymentToken
     * @param Address|null $shippingAddress
     * @param Address|null $billingAddress
     * @param ShippingMethodInterface|null $shippingMethod
     * @return InstantPurchaseOption
     * @since 100.2.0
     */
    public function create(
        ?PaymentTokenInterface $paymentToken = null,
        ?Address $shippingAddress = null,
        ?Address $billingAddress = null,
        ?ShippingMethodInterface $shippingMethod = null
    ): InstantPurchaseOption {
        return $this->objectManager->create(InstantPurchaseOption::class, [
            'paymentToken' => $paymentToken,
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
            'shippingMethod' => $shippingMethod,
        ]);
    }

    /**
     * Creates new empty instance (no option available).
     *
     * @return InstantPurchaseOption
     * @since 100.2.0
     */
    public function createDisabledOption(): InstantPurchaseOption
    {
        return $this->create(null, null, null, null);
    }
}
