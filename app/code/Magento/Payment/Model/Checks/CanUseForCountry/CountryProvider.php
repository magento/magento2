<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks\CanUseForCountry;

use Magento\Quote\Model\Quote;
use Magento\Directory\Helper\Data as DirectoryHelper;

/**
 * Select country which will be used for payment.
 *
 * This class may be extended if logic fo country selection should be modified.
 *
 * @api
 * @since 2.0.0
 */
class CountryProvider
{
    /**
     * @var DirectoryHelper
     * @since 2.0.0
     */
    protected $directoryHelper;

    /**
     * @param DirectoryHelper $directoryHelper
     * @since 2.0.0
     */
    public function __construct(DirectoryHelper $directoryHelper)
    {
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Get payment country
     *
     * @param Quote $quote
     * @return int
     * @since 2.0.0
     */
    public function getCountry(Quote $quote)
    {
        $address = $quote->getBillingAddress() ? : $quote->getShippingAddress();
        return (!empty($address) && !empty($address->getCountry()))
            ? $address->getCountry()
            : $this->directoryHelper->getDefaultCountry();
    }
}
