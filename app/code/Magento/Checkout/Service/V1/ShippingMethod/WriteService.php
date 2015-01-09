<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\ShippingMethod;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Sales\Model\QuoteRepository;

/**
 * Shipping method write service object.
 */
class WriteService implements WriteServiceInterface
{
    /**
     * Address factory.
     *
     * @var \Magento\Sales\Model\Quote\AddressFactory
     */
    protected $addressFactory;

    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Constructs a shipping method write service object.
     *
     * @param \Magento\Sales\Model\Quote\AddressFactory $addressFactory Address factory.
     * @param QuoteRepository $quoteRepository Quote repository.
     */
    public function __construct(
        \Magento\Sales\Model\Quote\AddressFactory $addressFactory,
        QuoteRepository $quoteRepository
    ) {
        $this->addressFactory = $addressFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The shopping cart ID.
     * @param string $carrierCode The carrier code.
     * @param string $methodCode The shipping method code.
     * @return bool
     * @throws \Magento\Framework\Exception\InputException The shipping method is not valid for an empty cart.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The shipping method could not be saved.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart contains only virtual products and the shipping method is not applicable.
     * @throws \Magento\Framework\Exception\StateException The billing or shipping address is not set.
     */
    public function setMethod($cartId, $carrierCode, $methodCode)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (0 == $quote->getItemsCount()) {
            throw new InputException('Shipping method is not applicable for empty cart');
        }

        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                'Cart contains virtual product(s) only. Shipping method is not applicable.'
            );
        }
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException('Shipping address is not set');
        }
        $billingAddress = $quote->getBillingAddress();
        if (!$billingAddress->getCountryId()) {
            throw new StateException('Billing address is not set');
        }

        $shippingAddress->setShippingMethod($carrierCode . '_' . $methodCode);
        if (!$shippingAddress->requestShippingRates()) {
            throw new NoSuchEntityException('Carrier with such method not found: ' . $carrierCode . ', ' . $methodCode);
        }
        try {
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Cannot set shipping method. ' . $e->getMessage());
        }
        return true;
    }
}
