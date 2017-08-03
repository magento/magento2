<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

/**
 * Configured postcode validation patterns
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Returns array of postcodes validation patterns
     *
     * @return array
     * @since 2.0.0
     */
    public function getPostCodes();
}
