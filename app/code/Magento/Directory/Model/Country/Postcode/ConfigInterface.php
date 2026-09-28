<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Model\Country\Postcode;

/**
 * Configured postcode validation patterns
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Returns array of postcodes validation patterns
     *
     * @return array
     */
    public function getPostCodes();
}
