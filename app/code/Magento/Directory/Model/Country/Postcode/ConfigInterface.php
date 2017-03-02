<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
