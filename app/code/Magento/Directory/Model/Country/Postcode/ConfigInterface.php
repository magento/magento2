<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

interface ConfigInterface
{

    /**
     * Returns array of postcodes validation patterns
     *
     * @return array
     */
    public function getPostCodes();
}
