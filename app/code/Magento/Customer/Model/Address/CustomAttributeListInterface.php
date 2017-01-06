<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address;

interface CustomAttributeListInterface
{
    /**
     * Retrieve list of customer addresses custom attributes
     *
     * @return array
     */
    public function getAttributes();
}
