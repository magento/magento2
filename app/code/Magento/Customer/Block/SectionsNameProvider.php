<?php

namespace Magento\Customer\Block;

use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel to get sections names array.
 */
class SectionsNameProvider implements ArgumentInterface
{
    /**
     * @var SectionPoolInterface
     */
    private $sectionPool;

    /**
     * @param SectionPoolInterface $sectionPool
     */
    public function __construct(
        SectionPoolInterface $sectionPool
    ) {
        $this->sectionPool = $sectionPool;
    }

    /**
     * Return array of section names.
     *
     * @return array
     */
    public function getSectionsName()
    {
        return array_keys($this->sectionPool->getSectionsData());
    }
}
