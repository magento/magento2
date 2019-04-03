<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\QuoteAddress;

use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Validate Quote Address
 */
class Validator
{
    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @param AddressHelper $addressHelper
     */
    public function __construct(AddressHelper $addressHelper)
    {
        $this->addressHelper = $addressHelper;
    }

    /**
     * Additional Quote Address validation for the GraphQl endpoint
     *
     * @param QuoteAddress $quoteAddress
     * @throws GraphQlInputException
     */
    public function validate(QuoteAddress $quoteAddress)
    {
        $maxAllowedLineCount = $this->addressHelper->getStreetLines();
        if (is_array($quoteAddress->getStreet()) && count($quoteAddress->getStreet()) > $maxAllowedLineCount) {
            throw new GraphQlInputException(__('"Street Address" cannot contain more than %1 lines.', $maxAllowedLineCount));
        }
    }
}
