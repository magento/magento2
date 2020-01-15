<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Exception;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Psr\Log\LoggerInterface;

/**
 * Quote billing address write service object.
 */
class BillingAddressManagement implements BillingAddressManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ShippingAddressAssignment
     */
    private $shippingAddressAssignment;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructs a quote billing address service object.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param ShippingAddressAssignment $shippingAddressAssignment
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ShippingAddressAssignment $shippingAddressAssignment,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->shippingAddressAssignment = $shippingAddressAssignment;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function assign($cartId, AddressInterface $address, $useForShipping = false)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $address->setCustomerId($quote->getCustomerId());
        $quote->removeAddress($quote->getBillingAddress()->getId());
        $quote->setBillingAddress($address);
        try {
            $this->shippingAddressAssignment->setAddress($quote, $address, $useForShipping);
            $quote->setDataChanges(true);
            $this->quoteRepository->save($quote);
        } catch (Exception $e) {
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
}
