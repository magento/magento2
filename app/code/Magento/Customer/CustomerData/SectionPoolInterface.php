<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\CustomerData;

/**
 * Section pool interface
 *
 * @api
 */
interface SectionPoolInterface
{
    /**
     * Get section data by section names. If $sectionNames is null then return all sections data
     *
     * @param array $sectionNames
     * @param bool $forceNewTimestamp
     * @return array
     */
    public function getSectionsData(?array $sectionNames = null, $forceNewTimestamp = false);
}
