<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\Quote\Address\BillingAddressPersister;
use Psr\Log\LoggerInterface as Logger;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Quote billing address write service object.
 *
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
     * Logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Quote repository.
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
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address, $useForShipping = false)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->removeAddress($quote->getBillingAddress()->getId());
        $quote->setBillingAddress($address);
        try {
            $this->getShippingAddressAssignment()->setAddress($quote, $address, $useForShipping);
            $quote->setDataChanges(true);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(__('Unable to save address. Please check input data.'));
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

    /**
     * @return \Magento\Quote\Model\ShippingAddressAssignment
     * @deprecated 100.2.0
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
