<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Address\Billing;

use Magento\Checkout\Service\V1\Address\Converter as AddressConverter;

/** Quote billing address read service object. */
class ReadService implements ReadServiceInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Address converter.
     *
     * @var AddressConverter
     */
    protected $addressConverter;

    /**
     * Constructs a quote billing address object.
     *
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository Quote repository.
     * @param AddressConverter $addressConverter Address converter.
     */
    public function __construct(
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        AddressConverter $addressConverter
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressConverter = $addressConverter;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\Address Quote billing address object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getAddress($cartId)
    {
        /**
         * Address.
         *
         * @var  \Magento\Sales\Model\Quote\Address $address
         */
        $address = $this->quoteRepository->getActive($cartId)->getBillingAddress();
        return $this->addressConverter->convertModelToDataObject($address);
    }
}
