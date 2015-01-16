<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Address\Billing;

use Magento\Checkout\Service\V1\Address\Converter;
use Magento\Checkout\Service\V1\Address\Validator;
use Magento\Sales\Model\Quote\AddressFactory;
use Magento\Sales\Model\QuoteRepository;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface as Logger;

/** Quote billing address write service object. */
class WriteService implements WriteServiceInterface
{
    /**
     * Validator.
     *
     * @var Validator
     */
    protected $addressValidator;

    /**
     * Logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Address factory.
     *
     * @var AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * Converter.
     *
     * @var Converter
     */
    protected $addressConverter;

    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Constructs a quote billing address service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param Converter $addressConverter Address converter.
     * @param Validator $addressValidator Address validator.
     * @param AddressFactory $quoteAddressFactory Quote address factory.
     * @param Logger $logger Logger.
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Converter $addressConverter,
        Validator $addressValidator,
        AddressFactory $quoteAddressFactory,
        Logger $logger
    ) {
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->addressConverter = $addressConverter;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Address $addressData Billing address data.
     * @return int Address ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException The specified cart ID or address data is not valid.
     */
    public function setAddress($cartId, $addressData)
    {
        /**
         * Quote.
         *
         * @var \Magento\Sales\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        /**
         * Address.
         *
         * @var \Magento\Sales\Model\Quote\Address $address
         */
        $address = $this->quoteAddressFactory->create();
        $this->addressValidator->validate($addressData);
        if ($addressData->getId()) {
            $address->load($addressData->getId());
        }
        $address = $this->addressConverter->convertDataObjectToModel($addressData, $address);
        $quote->setBillingAddress($address);
        $quote->setDataChanges(true);
        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException('Unable to save address. Please, check input data.');
        }
        return $quote->getBillingAddress()->getId();
    }
}
