<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model\Address;

/**
 * @api
 * @since 100.0.6
 */
interface CustomAttributeListInterface
{
    /**
     * Retrieve list of customer addresses custom attributes
     *
     * @return array
     * @since 100.0.6
     */
    public function getAttributes();
}
