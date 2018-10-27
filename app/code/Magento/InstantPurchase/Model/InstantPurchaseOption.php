<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Model\Address;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use InvalidArgumentException;

/**
 * Option to make instant purchase.
 *
 * @api
 */
class InstantPurchaseOption
{
    /**
     * @var PaymentTokenInterface|null
     */
    private $paymentToken;

    /**
     * @var Address|null
     */
    private $shippingAddress;

    /**
     * @var Address|null
     */
    private $billingAddress;

    /**
     * @var ShippingMethodInterface|null
     */
    private $shippingMethod;

    /**
     * InstantPurchaseOption constructor.
     * @param PaymentTokenInterface|null $paymentToken
     * @param Address|null $shippingAddress
     * @param Address|null $billingAddress
     * @param ShippingMethodInterface|null $shippingMethod
     * @throws InvalidArgumentException if invalid data provided (implementation error)
     *
     * @SuppressWarnings(Magento.TypeDuplication)
     * Type duplication verified.
     * This is not a service class and should not be instantiated directly through Object Manager.
     * Use InstantPurchaseOptionFactory instead.
     */
    public function __construct(
        PaymentTokenInterface $paymentToken = null,
        Address $shippingAddress = null,
        Address $billingAddress = null,
        ShippingMethodInterface $shippingMethod = null
    ) {
        $customers = [];
        if ($paymentToken) {
            $customers[] = $paymentToken->getCustomerId();
        }
        if ($shippingAddress) {
            $customers[] = $shippingAddress->getCustomerId();
        }
        if ($billingAddress) {
            $customers[] = $billingAddress->getCustomerId();
        }
        if (count(array_unique($customers)) > 1) {
            throw new InvalidArgumentException('Provided data does not belong to same customer.');
        }

        $this->paymentToken = $paymentToken;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->shippingMethod = $shippingMethod;
    }

    /**
     * Checks if option available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return isset(
            $this->paymentToken,
            $this->shippingAddress,
            $this->billingAddress,
            $this->shippingMethod
        ) && $this->shippingMethod->getAvailable();
    }

    /**
     * Returns payment token for instant purchase.
     *
     * @return PaymentTokenInterface
     * @throws LocalizedException if payment token is not defined
     */
    public function getPaymentToken(): PaymentTokenInterface
    {
        if (!isset($this->paymentToken)) {
            throw new LocalizedException(__('Payment method is not defined for instance purchase.'));
        }
        return $this->paymentToken;
    }

    /**
     * Returns shipping address for instant purchase.
     *
     * @return Address
     * @throws LocalizedException if shipping address is not defined
     */
    public function getShippingAddress(): Address
    {
        if (!isset($this->shippingAddress)) {
            throw new LocalizedException(__('Shipping address is not defined for instance purchase.'));
        }
        return $this->shippingAddress;
    }

    /**
     * Returns billing address for instant purchase.
     *
     * @return Address
     * @throws LocalizedException if billing address is not defined
     */
    public function getBillingAddress(): Address
    {
        if (!isset($this->billingAddress)) {
            throw new LocalizedException(__('Billing address is not defined for instance purchase.'));
        }
        return $this->billingAddress;
    }

    /**
     * Returns shipping method for instant purchase.
     *
     * @return ShippingMethodInterface
     * @throws LocalizedException if shipping method is not defined
     */
    public function getShippingMethod(): ShippingMethodInterface
    {
        if (!isset($this->shippingMethod)) {
            throw new LocalizedException(__('Shipping method is not defined for instance purchase.'));
        }
        return $this->shippingMethod;
    }
}
