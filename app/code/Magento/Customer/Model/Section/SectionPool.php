<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Section;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Section pool
 */
class SectionPool implements SectionPoolInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Section map. Key is section name, value is section object class
     *
     * @var array
     */
    protected $sectionMap;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $sectionMap
     */
    public function __construct(ObjectManagerInterface $objectManager, array $sectionMap = [])
    {
        $this->objectManager = $objectManager;
        $this->sectionMap = $sectionMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllSections()
    {
        $sections = [];
        foreach ($this->sectionMap as $sectionName => $sectionClass) {
            $sections[$sectionName] = $this->get($sectionClass);
        }
        return $sections;
    }

    /**
     * {@inheritdoc}
     */
    public function getSections(array $sectionNames)
    {
        $sections = [];
        foreach ($sectionNames as $sectionName) {
            if (!isset($this->sectionMap[$sectionName])) {
                throw new LocalizedException('"' . $sectionName . '" section is not supported');
            }
            $sections[$sectionName] = $this->get($this->sectionMap[$sectionName]);
        }
        return $sections;
    }

    /**
     * Get section by class
     *
     * @param string $sectionClass
     * @return SectionPoolInterface
     * @throws LocalizedException
     */
    protected function get($sectionClass)
    {
        $section = $this->objectManager->get($sectionClass);

        if (!$section instanceof SectionInterface) {
            throw new LocalizedException(
                $sectionClass . ' doesn\'t extends \Magento\Customer\Model\Section\SectionInterface'
            );
        }
        return $section;
    }
}
