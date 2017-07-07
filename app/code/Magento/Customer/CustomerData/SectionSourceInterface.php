<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

/**
 * Section source interface
 *
 * @api Use to define data sections in customer data which are transported from backend to frontend local storage
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
