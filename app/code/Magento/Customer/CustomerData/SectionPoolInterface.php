<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

/**
 * Section pool interface
 * @since 2.0.0
 */
interface SectionPoolInterface
{
    /**
     * Get section data by section names. If $sectionNames is null then return all sections data
     *
     * @param array $sectionNames
     * @param bool $updateIds
     * @return array
     * @since 2.0.0
     */
    public function getSectionsData(array $sectionNames = null, $updateIds = false);
}
