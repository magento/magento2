<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

/**
 * Section pool interface
 *
 * @api Use to collect data sections in customer data which are transported from backend to frontend local storage
 */
interface SectionPoolInterface
{
    /**
     * Get section data by section names. If $sectionNames is null then return all sections data
     *
     * @param array $sectionNames
     * @param bool $updateIds
     * @return array
     */
    public function getSectionsData(array $sectionNames = null, $updateIds = false);
}
