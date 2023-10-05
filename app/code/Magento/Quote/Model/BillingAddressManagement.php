<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Quote billing address write service object.
 */
class BillingAddressManagement implements BillingAddressManagementInterface
{
    /**
     * Validator.
     *
     * @var QuoteAddressValidator
     */
    protected $addressValidator;

    /**
     * Logger object.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Quote repository object.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Quote\Model\ShippingAddressAssignment
     */
    private $shippingAddressAssignment;

    /**
     * Constructs a quote billing address service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository Quote repository.
     * @param QuoteAddressValidator $addressValidator Address validator.
     * @param Logger $logger Logger.
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        Logger $logger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function assign($cartId, AddressInterface $address, $useForShipping = false)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // validate the address
        $this->addressValidator->validateWithExistingAddress($quote, $address);

        $address->setCustomerId($quote->getCustomerId());
        $quote->removeAddress($quote->getBillingAddress()->getId());
        $quote->setBillingAddress($address);
        try {
            $this->getShippingAddressAssignment()->setAddress($quote, $address, $useForShipping);
            $quote->setDataChanges(true);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(__('The address failed to save. Verify the address and try again.'));
        }
        return $quote->getBillingAddress()->getId();
    }

    /**
     * @inheritdoc
     */
    public function get($cartId)
    {
        $cart = $this->quoteRepository->getActive($cartId);
        return $cart->getBillingAddress();
    }

    /**
     * Get shipping address assignment
     *
     * @return \Magento\Quote\Model\ShippingAddressAssignment
     * @deprecated 101.0.0
     * @see \Magento\Quote\Model\Quote\Address
     */
    private function getShippingAddressAssignment()
    {
        if (!$this->shippingAddressAssignment) {
            $this->shippingAddressAssignment = ObjectManager::getInstance()
                ->get(\Magento\Quote\Model\ShippingAddressAssignment::class);
        }
        return $this->shippingAddressAssignment;
    }
}
