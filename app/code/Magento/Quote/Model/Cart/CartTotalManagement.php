<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\CartTotalManagementInterface;

/**
 * Cart totals data object.
 */
class CartTotalManagement implements CartTotalManagementInterface
{
    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    protected $shippingMehotManagement;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     */
    public function __construct(
        \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
    ) {
        $this->shippingMehotManagement = $shippingMethodManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartTotalsRepository = $cartTotalsRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function collectTotals(
        $cartId,
        $shippingCarrierCode = null,
        $shippingMethodCode = null,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    ) {
        if ($shippingCarrierCode && $shippingMethodCode) {
            $this->shippingMehotManagement->set($cartId, $shippingCarrierCode, $shippingMethodCode);
        }
        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        return $this->cartTotalsRepository->get($cartId);
    }
}
