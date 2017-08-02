<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

/**
 * Interface \Magento\Directory\Model\Country\Postcode\ValidatorInterface
 *
 * @since 2.0.0
 */
interface ValidatorInterface
{
    /**
     * Validate postcode for selected country by mask
     *
     * @param string $postcode
     * @param string $countryId
     * @return bool
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function validate($postcode, $countryId);
}
