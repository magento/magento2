<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Address\Shipping;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface as Logger;

/** Quote shipping address write service object. */
class WriteService implements WriteServiceInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Quote address factory.
     *
     * @var \Magento\Sales\Model\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * Address converter.
     *
     * @var \Magento\Checkout\Service\V1\Address\Converter
     */
    protected $addressConverter;

    /**
     * Address validator.
     *
     * @var \Magento\Checkout\Service\V1\Address\Validator
     */
    protected $addressValidator;

    /**
     * Logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Constructs a quote shipping address write service object.
     *
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository Quote repository.
     * @param \Magento\Checkout\Service\V1\Address\Converter $addressConverter Address converter.
     * @param \Magento\Checkout\Service\V1\Address\Validator $addressValidator Address validator.
     * @param \Magento\Sales\Model\Quote\AddressFactory $quoteAddressFactory Quote address factory.
     * @param Logger $logger Logger.
     */
    public function __construct(
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        \Magento\Checkout\Service\V1\Address\Converter $addressConverter,
        \Magento\Checkout\Service\V1\Address\Validator $addressValidator,
        \Magento\Sales\Model\Quote\AddressFactory $quoteAddressFactory,
        Logger $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->addressConverter = $addressConverter;
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Address $addressData The shipping address data.
     * @return int Address ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException The specified cart ID or address data is not valid.
     */
    public function setAddress($cartId, $addressData)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                'Cart contains virtual product(s) only. Shipping address is not applicable'
            );
        }
        /** @var \Magento\Sales\Model\Quote\Address $address */
        $address = $this->quoteAddressFactory->create();
        $this->addressValidator->validate($addressData);
        if ($addressData->getId()) {
            $address->load($addressData->getId());
        }
        $address = $this->addressConverter->convertDataObjectToModel($addressData, $address);
        $address->setSameAsBilling(0);
        $address->setCollectShippingRates(true);

        $quote->setShippingAddress($address);
        $quote->setDataChanges(true);
        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException('Unable to save address. Please, check input data.');
        }
        return $quote->getShippingAddress()->getId();
    }
}
