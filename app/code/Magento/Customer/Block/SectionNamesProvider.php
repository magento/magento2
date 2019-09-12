<?php

namespace Magento\Customer\Block;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel to get sections names array.
 */
class SectionNamesProvider implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    private $sectionConfig;

    /**
     * @param \Magento\Framework\Config\DataInterface $sectionConfig
     */
    public function __construct(
        DataInterface $sectionConfig
    ) {
        $this->sectionConfig = $sectionConfig->get('sections');
    }

    /**
     * Return array of section names based on config.
     *
     * @return array
     */
    public function getSectionNames()
    {
        $resultSectionNames = [];
        foreach ($this->sectionConfig as $sectionRule => $sectionNames) {
            if (is_array($sectionNames)) {
                $resultSectionNames = array_merge($resultSectionNames, $sectionNames);
            }
        }

        if ($allNameIndex = array_search('*', $resultSectionNames, true)) {
            unset($resultSectionNames[$allNameIndex]);
        }

        return array_values(array_unique($resultSectionNames));
    }
}
