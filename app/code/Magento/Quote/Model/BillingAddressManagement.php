<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Quote billing address write service object.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BillingAddressManagement implements BillingAddressManagementInterface
{
    /**
     * Billing address lock const
     *
     * @var string
     */
    private const CART_BILLING_ADDRESS_LOCK = 'cart_billing_address_lock_';

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
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Quote\Model\ShippingAddressAssignment
     */
    private $shippingAddressAssignment;

    /**
     * @var CartAddressMutexInterface
     */
    private $cartAddressMutex;

    /**
     * Constructs a quote billing address service object.
     *
     * @param CartRepositoryInterface $quoteRepository Quote repository.
     * @param QuoteAddressValidator $addressValidator Address validator.
     * @param Logger $logger Logger.
     * @param AddressRepositoryInterface $addressRepository
     * @param CartAddressMutexInterface|null $cartAddressMutex
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        Logger $logger,
        AddressRepositoryInterface $addressRepository,
        ?CartAddressMutexInterface $cartAddressMutex = null
    ) {
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
        $this->cartAddressMutex = $cartAddressMutex ??
            ObjectManager::getInstance()->get(CartAddressMutex::class);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function assign($cartId, AddressInterface $address, $useForShipping = false)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $billingAddressId = (int) $quote->getBillingAddress()->getId();

        return $this->cartAddressMutex->execute(
            self::CART_BILLING_ADDRESS_LOCK.$billingAddressId,
            $this->assignBillingAddress(...),
            $billingAddressId,
            [$address, $quote, $useForShipping]
        );
    }

    /**
     * Assign billing address to cart
     *
     * @param AddressInterface $address
     * @param Quote $quote
     * @param bool $useForShipping
     * @return mixed
     * @throws InputException
     */
    public function assignBillingAddress(AddressInterface $address, Quote $quote, bool $useForShipping = false)
    {
        $address->setCustomerId($quote->getCustomerId());
        $quote->removeAddress($quote->getBillingAddress()->getId());
        $quote->setBillingAddress($address);
        try {
            $this->getShippingAddressAssignment()->setAddress($quote, $address, $useForShipping);
            $quote->setDataChanges(true);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
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
     * @see \Magento\Quote\Model\ShippingAddressAssignment
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
