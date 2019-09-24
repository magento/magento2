<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

use Magento\Customer\CustomerData\SectionPool;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel to get sections names array.
 */
class SectionNamesProvider implements ArgumentInterface
{
    /**
     * @var SectionPool
     */
    private $sectionPool;

    /**
     * @param SectionPool $sectionPool
     */
    public function __construct(
        SectionPool $sectionPool
    ) {
        $this->sectionPool = $sectionPool;
    }

    /**
     * Return array of section names based on config.
     *
     * @return array
     */
    public function getSectionNames()
    {
        return $this->sectionPool->getSectionNames();
    }
}
