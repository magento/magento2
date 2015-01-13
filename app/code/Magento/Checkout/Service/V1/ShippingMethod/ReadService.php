<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\ShippingMethod;

use Magento\Checkout\Service\V1\Data\Cart\ShippingMethod;
use Magento\Checkout\Service\V1\Data\Cart\ShippingMethodBuilder;
use Magento\Checkout\Service\V1\Data\Cart\ShippingMethodConverter;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Sales\Model\QuoteRepository;

/**
 * Shipping method read service.
 */
class ReadService implements ReadServiceInterface
{
    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Shipping method builder.
     *
     * @var \Magento\Checkout\Service\V1\Data\Cart\ShippingMethodBuilder
     */
    protected $methodBuilder;

    /**
     * Shipping method converter.
     *
     * @var ShippingMethodConverter
     */
    protected $converter;

    /**
     * Constructs a shipping method read service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param ShippingMethodConverter $converter Shipping method converter.
     * @param \Magento\Checkout\Service\V1\Data\Cart\ShippingMethodBuilder $methodBuilder Shipping method builder.
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        ShippingMethodConverter $converter,
        \Magento\Checkout\Service\V1\Data\Cart\ShippingMethodBuilder $methodBuilder
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->converter = $converter;
        $this->methodBuilder = $methodBuilder;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\ShippingMethod Shipping method.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified shopping cart does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is not set.
     */
    public function getMethod($cartId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        /** @var \Magento\Sales\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException('Shipping address not set.');
        }

        $shippingMethod = $shippingAddress->getShippingMethod();
        if (!$shippingMethod) {
            return null;
        }

        list($carrierCode, $methodCode) = $this->divideNames('_', $shippingAddress->getShippingMethod());
        list($carrierTitle, $methodTitle) = $this->divideNames(' - ', $shippingAddress->getShippingDescription());

        $output = [
            ShippingMethod::CARRIER_CODE => $carrierCode,
            ShippingMethod::METHOD_CODE => $methodCode,
            ShippingMethod::CARRIER_TITLE => $carrierTitle,
            ShippingMethod::METHOD_TITLE => $methodTitle,
            ShippingMethod::SHIPPING_AMOUNT => $shippingAddress->getShippingAmount(),
            ShippingMethod::BASE_SHIPPING_AMOUNT => $shippingAddress->getBaseShippingAmount(),
            ShippingMethod::AVAILABLE => true,
        ];

        return $this->methodBuilder->populateWithArray($output)->create();
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
            throw new InputException('Line "' .  $line . '" doesn\'t contain delimiter ' . $delimiter);
        }
        return explode($delimiter, $line);
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\ShippingMethod[] An array of shipping methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified quote does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is not set.
     */
    public function getList($cartId)
    {
        $output = [];

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException('Shipping address not set.');
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
}
