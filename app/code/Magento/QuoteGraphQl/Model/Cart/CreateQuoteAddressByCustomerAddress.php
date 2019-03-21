<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Creates a quote address based on given context, customer address ID and customer address
 */
class CreateQuoteAddressByCustomerAddress
{
    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var GetCustomerAddress
     */
    private $getCustomerAddress;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param GetCustomer $getCustomer
     * @param GetCustomerAddress $getCustomerAddress
     */
    public function __construct(
        QuoteAddressFactory $quoteAddressFactory,
        GetCustomer $getCustomer,
        GetCustomerAddress $getCustomerAddress
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->getCustomer = $getCustomer;
        $this->getCustomerAddress = $getCustomerAddress;
    }

    /**
     * @param ContextInterface $context
     * @param int|string|null $customerAddressId
     * @param array|null $customerAddress
     *
     * @return Address
     */
    public function execute(
        ContextInterface $context,
        $customerAddressId,
        $customerAddress
    ): Address {
        if (null === $customerAddressId) {
            return  $this->quoteAddressFactory->createBasedOnInputData($customerAddress);
        }

        $customer = $this->getCustomer->execute($context);
        $customerAddress = $this->getCustomerAddress->execute((int)$customerAddressId, (int)$customer->getId());

        return $this->quoteAddressFactory->createBasedOnCustomerAddress($customerAddress);
    }
}
