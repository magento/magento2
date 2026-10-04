<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * Interface \Magento\Quote\Model\Quote\Address\CustomAttributeListInterface
 *
 * @api
 */
interface CustomAttributeListInterface
{
    /**
     * Retrieve list of quote address custom attributes
     *
     * @return array
     */
    public function getAttributes();
}
