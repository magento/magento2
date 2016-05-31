<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

/**
 * Section source interface
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
