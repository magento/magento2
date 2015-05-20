<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Model\AddressAdditionalDataProcessor;

class AddressDetailsManagement implements \Magento\Quote\Api\AddressDetailsManagementInterface
{
    /**
     * @var \Magento\Quote\Api\BillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Quote\Api\ShippingAddressManagementInterface
     */
    protected $shippingAddressManagement;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var AddressDetailsFactory
     */
    protected $addressDetailsFactory;

    /**
     * @var AddressAdditionalDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @param \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\ShippingAddressManagementInterface $shippingAddressManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement
     * @param AddressDetailsFactory $addressDetailsFactory
     * @param AddressAdditionalDataProcessor $dataProcessor
     * @param QuoteRepository $quoteRepository
     * @param CartTotalRepositoryInterface $cartTotalsRepository
     */
    public function __construct(
        \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\ShippingAddressManagementInterface $shippingAddressManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement,
        \Magento\Quote\Model\AddressDetailsFactory $addressDetailsFactory,
        AddressAdditionalDataProcessor $dataProcessor,
        QuoteRepository $quoteRepository,
        CartTotalRepositoryInterface $cartTotalsRepository
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->addressDetailsFactory = $addressDetailsFactory;
        $this->dataProcessor = $dataProcessor;
        $this->quoteRepository = $quoteRepository;
        $this->cartTotalsRepository = $cartTotalsRepository;
    }

    /**
     * @{inheritdoc}
     */
    public function saveAddresses(
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress,
        \Magento\Quote\Api\Data\AddressInterface $shippingAddress = null,
        \Magento\Quote\Api\Data\AddressAdditionalDataInterface $additionalData = null,
        $checkoutMethod = null
    ) {
        $this->billingAddressManagement->assign($cartId, $billingAddress);

        /** @var \Magento\Quote\Api\Data\AddressDetailsInterface  $addressDetails */
        $addressDetails = $this->addressDetailsFactory->create();
        if ($shippingAddress) {
            $this->shippingAddressManagement->assign($cartId, $shippingAddress);
            $addressDetails->setFormattedShippingAddress(
                $this->shippingAddressManagement->get($cartId)->format('html')
            );
            $addressDetails->setShippingMethods($this->shippingMethodManagement->getList($cartId));
        }
        $addressDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        if ($additionalData !== null) {
            $this->dataProcessor->process($additionalData);
        }
        if ($checkoutMethod != null) {
            $this->quoteRepository->save(
                $this->quoteRepository->getActive($cartId)
                    ->setCheckoutMethod($checkoutMethod)
            );
        }

        $addressDetails->setFormattedBillingAddress(
            $this->billingAddressManagement->get($cartId)->format('html')
        );

        $addressDetails->setTotals($this->cartTotalsRepository->get($cartId));
        return $addressDetails;
    }
}
