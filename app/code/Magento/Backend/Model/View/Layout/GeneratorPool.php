<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\App\ObjectManager;

/**
 * Pool of generators for structural elements
 */
class GeneratorPool extends \Magento\Framework\View\Layout\GeneratorPool
{
    /**
     * @var FilterInterface
     */
    private $filter;


    /**
     * @return FilterInterface
     */
    private function getFilter()
    {
        if (!$this->filter) {
            $this->filter = ObjectManager::getInstance()->get(FilterInterface::class);
        }
        return $this->filter;
    }

    /**
     * Build structure that is based on scheduled structure
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @return $this
     */
    protected function buildStructure(ScheduledStructure $scheduledStructure, Structure $structure)
    {
        parent::buildStructure($scheduledStructure, $structure);
        $this->getFilter()->filterElement($scheduledStructure, $structure);
        return $this;
    }
}
