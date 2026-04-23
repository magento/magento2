<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Model\Country\Postcode;

/**
 * Interface \Magento\Directory\Model\Country\Postcode\ValidatorInterface
 *
 * @api
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
     */
    public function validate($postcode, $countryId);
}
