<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Section;

/**
 * Section pool interface
 */
interface SectionPoolInterface
{
    /**
     * Get all sections
     *
     * @return SectionInterface[]
     */
    public function getAllSections();

    /**
     * Get sections by section names
     *
     * @param array $sectionNames
     * @return SectionInterface[]
     */
    public function getSections(array $sectionNames);
}
