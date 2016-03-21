<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

interface CustomAttributeListInterface
{
    /**
     * Retrieve list of quote addresss custom attributes
     *
     * @return array
     */
    public function getAttributes();
}
