<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;

class GuestShippingInformationManagement implements \Magento\Checkout\Api\GuestShippingInformationManagementInterface
{
    /**
     * @var \Magento\Quote\Api\GuestShippingAddressManagementInterface
     */
    protected $shippingAddressManagement;

    /**
     * @var \Magento\Quote\Api\GuestShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var PaymentDetailsFactory
     */
    protected $paymentDetailsFactory;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @param \Magento\Quote\Api\GuestShippingAddressManagementInterface $shippingAddressManagement
     * @param \Magento\Quote\Api\GuestShippingMethodManagementInterface $shippingMethodManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param PaymentDetailsFactory $paymentDetailsFactory
     * @param CartTotalRepositoryInterface $cartTotalsRepository
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Magento\Quote\Api\GuestShippingAddressManagementInterface $shippingAddressManagement,
        \Magento\Quote\Api\GuestShippingMethodManagementInterface $shippingMethodManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        CartTotalRepositoryInterface $cartTotalsRepository,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function saveAddressInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $this->shippingAddressManagement->assign($cartId, $addressInformation->getShippingAddress());
        $this->shippingMethodManagement->set(
            $cartId,
            $addressInformation->getShippingCarrierCode(),
            $addressInformation->getShippingMethodCode()
        );

        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        /** @var PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($quoteIdMask->getQuoteId()));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($quoteIdMask->getQuoteId()));
        return $paymentDetails;
    }
}
