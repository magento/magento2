<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\CustomerData;

/**
 * Section source interface
 *
 * @api Use to define data sections in customer data which are transported from backend to frontend local storage
 * @since 100.0.2
 */
interface SectionSourceInterface
{
    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData();
}
