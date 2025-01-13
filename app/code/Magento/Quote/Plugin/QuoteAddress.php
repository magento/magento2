<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\QuoteAddressValidator;

/**
 * Quote address plugin
 */
class QuoteAddress
{
    /**
     * @var QuoteAddressValidator
     */
    protected QuoteAddressValidator $addressValidator;

    /**
     * @param QuoteAddressValidator $addressValidator
     */
    public function __construct(
        QuoteAddressValidator $addressValidator
    ) {
        $this->addressValidator = $addressValidator;
    }

    /**
     * Validate address before setting billing address
     *
     * @param Quote $subject
     * @param AddressInterface|null $address
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeSetBillingAddress(Quote $subject, AddressInterface $address = null): array
    {
        if ($address !== null) {
            $this->addressValidator->validateWithExistingAddress($subject, $address);
        }

        return [$address];
    }

    /**
     * Validate address before setting shipping address
     *
     * @param Quote $subject
     * @param AddressInterface|null $address
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeSetShippingAddress(Quote $subject, AddressInterface $address = null): array
    {
        if ($address !== null) {
            $this->addressValidator->validateWithExistingAddress($subject, $address);
        }

        return [$address];
    }
}
