<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;

/**
 * Shipping method read service.
 */
class ShippingMethodManagement implements ShippingMethodManagementInterface
{
    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Shipping data factory.
     *
     * @var \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory
     */
    protected $methodDataFactory;

    /**
     * Shipping method converter
     *
     * @var \Magento\Quote\Model\Cart\ShippingMethodConverter
     */
    protected $converter;

    /**
     * Constructs a shipping method read service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $methodDataFactory Shipping method factory.
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $converter Shipping method converter.
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $methodDataFactory,
        Cart\ShippingMethodConverter $converter
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->methodDataFactory = $methodDataFactory;
        $this->converter = $converter;
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('Shipping address not set.'));
        }

        $shippingMethod = $shippingAddress->getShippingMethod();
        if (!$shippingMethod) {
            return null;
        }

        list($carrierCode, $methodCode) = $this->divideNames('_', $shippingAddress->getShippingMethod());
        list($carrierTitle, $methodTitle) = $this->divideNames(' - ', $shippingAddress->getShippingDescription());

        return $this->methodDataFactory->create()
            ->setCarrierCode($carrierCode)
            ->setMethodCode($methodCode)
            ->setCarrierTitle($carrierTitle)
            ->setMethodTitle($methodTitle)
            ->setAmount($shippingAddress->getShippingAmount())
            ->setBaseAmount($shippingAddress->getBaseShippingAmount())
            ->setAvailable(true);
    }

    /**
     * Divides names at specified delimiter character on a specified line.
     *
     * @param string $delimiter The delimiter character.
     * @param string $line The line.
     * @return array Array of names.
     * @throws \Magento\Framework\Exception\InputException The specified line does not contain the specified delimiter character.
     */
    protected function divideNames($delimiter, $line)
    {
        if (strpos($line, $delimiter) === false) {
            throw new InputException(
                __('Line "%1" doesn\'t contain delimiter %2', $line, $delimiter)
            );
        }
        return explode($delimiter, $line);
    }

    /**
     * {@inheritDoc}
     */
    public function getList($cartId)
    {
        $output = [];

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('Shipping address not set.'));
        }
        $shippingAddress->collectShippingRates();
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
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
    public function set($cartId, $carrierCode, $methodCode)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (0 == $quote->getItemsCount()) {
            throw new InputException(__('Shipping method is not applicable for empty cart'));
        }

        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                __('Cart contains virtual product(s) only. Shipping method is not applicable.')
            );
        }
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('Shipping address is not set'));
        }
        $billingAddress = $quote->getBillingAddress();
        if (!$billingAddress->getCountryId()) {
            throw new StateException(__('Billing address is not set'));
        }

        $shippingAddress->setShippingMethod($carrierCode . '_' . $methodCode);
        if (!$shippingAddress->requestShippingRates()) {
            throw new NoSuchEntityException(
                __('Carrier with such method not found: %1, %2', $carrierCode, $methodCode)
            );
        }
        try {
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Cannot set shipping method. %1', $e->getMessage()));
        }
        return true;
    }
}
