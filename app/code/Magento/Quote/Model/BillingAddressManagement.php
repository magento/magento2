<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface as Logger;
use Magento\Quote\Api\BillingAddressManagementInterface;

/** Quote billing address write service object. */
class BillingAddressManagement implements BillingAddressManagementInterface
{
    /**
     * Validator.
     *
     * @var QuoteAddressValidator
     */
    protected $addressValidator;

    /**
     * Logger.
     *
     * @var Logger
     */
    protected $logger;

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
     * @param QuoteAddressValidator $addressValidator Address validator.
     * @param Logger $logger Logger.
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        QuoteAddressValidator $addressValidator,
        Logger $logger
    ) {
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $this->addressValidator->validate($address);
        $quote->setBillingAddress($address);
        $quote->setDataChanges(true);
        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(__('Unable to save address. Please, check input data.'));
        }
        return $quote->getBillingAddress()->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        $cart = $this->quoteRepository->getActive($cartId);
        return $cart->getBillingAddress();
    }
}
