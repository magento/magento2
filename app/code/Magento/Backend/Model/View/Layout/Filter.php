<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\ScheduledStructure;

/**
 * Class Filter
 *
 * Composite filter for layout elements (blocks, uiComponents)
 * Is used for managing layout elements visibility at backend
 * Called at \Magento\Backend\Model\View\Layout\GeneratorPool::buildStructure
 *
 * @see details in \Magento\Backend\Model\View\Layout\FilterInterface
 */
class Filter implements FilterInterface
{
    /**
     * @var FilterInterface[]
     */
    private $filters;

    /**
     * Filter constructor.
     *
     * @param FilterInterface[] $filters
     */
    public function __construct(
        array $filters = []
    ) {
        $this->filters = $filters;
    }

    /**
     * Filter structure elements
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @return bool
     */
    public function filterElement(ScheduledStructure $scheduledStructure, Structure $structure)
    {
        foreach ($this->filters as $filter) {
            $filter->filterElement($scheduledStructure, $structure);
        }
        return true;
    }
}
