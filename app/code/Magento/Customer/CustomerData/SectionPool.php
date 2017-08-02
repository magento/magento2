<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Section pool
 *
 * @api
 * @since 2.0.0
 */
class SectionPool implements SectionPoolInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Section map. Key is section name, value is section source object class
     *
     * @var array
     * @since 2.0.0
     */
    protected $sectionSourceMap;

    /**
     * @var \Magento\Customer\CustomerData\Section\Identifier
     * @since 2.0.0
     */
    protected $identifier;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Customer\CustomerData\Section\Identifier $identifier
     * @param array $sectionSourceMap
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Customer\CustomerData\Section\Identifier $identifier,
        array $sectionSourceMap = []
    ) {
        $this->objectManager = $objectManager;
        $this->identifier = $identifier;
        $this->sectionSourceMap = $sectionSourceMap;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSectionsData(array $sectionNames = null, $updateIds = false)
    {
        $sectionsData = $sectionNames ? $this->getSectionDataByNames($sectionNames) : $this->getAllSectionData();
        $sectionsData = $this->identifier->markSections($sectionsData, $sectionNames, $updateIds);
        return $sectionsData;
    }

    /**
     * Get section sources by section names
     *
     * @param array $sectionNames
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function getSectionDataByNames($sectionNames)
    {
        $data = [];
        foreach ($sectionNames as $sectionName) {
            if (!isset($this->sectionSourceMap[$sectionName])) {
                throw new LocalizedException(__('"%1" section source is not supported', $sectionName));
            }
            $data[$sectionName] = $this->get($this->sectionSourceMap[$sectionName])->getSectionData();
        }
        return $data;
    }

    /**
     * Get all section sources
     *
     * @return array
     * @since 2.0.0
     */
    protected function getAllSectionData()
    {
        $data = [];
        foreach ($this->sectionSourceMap as $sectionName => $sectionClass) {
            $data[$sectionName] = $this->get($sectionClass)->getSectionData();
        }
        return $data;
    }

    /**
     * Get section source by name
     *
     * @param string $name
     * @return SectionSourceInterface
     * @throws LocalizedException
     * @since 2.0.0
     */
    protected function get($name)
    {
        $sectionSource = $this->objectManager->get($name);

        if (!$sectionSource instanceof SectionSourceInterface) {
            throw new LocalizedException(
                __('%1 doesn\'t extend \Magento\Customer\CustomerData\SectionSourceInterface', $name)
            );
        }
        return $sectionSource;
    }
}
