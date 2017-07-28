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
 * @since 2.0.0
 */
interface SectionSourceInterface
{
    /**
     * Get data
     *
     * @return array
     * @since 2.0.0
     */
    public function getSectionData();
}
