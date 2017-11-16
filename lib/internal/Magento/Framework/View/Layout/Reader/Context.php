<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page;

/**
 * @api
 */
class Context
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure
     */
    protected $scheduledStructure;

    /**
     * @var \Magento\Framework\View\Page\Config\Structure
     */
    protected $pageConfigStructure;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure $scheduledStructure
     * @param \Magento\Framework\View\Page\Config\Structure $pageConfigStructure
     */
    public function __construct(
        Layout\ScheduledStructure $scheduledStructure,
        Page\Config\Structure $pageConfigStructure
    ) {
        $this->scheduledStructure = $scheduledStructure;
        $this->pageConfigStructure = $pageConfigStructure;
    }

    /**
     * @return \Magento\Framework\View\Layout\ScheduledStructure
     */
    public function getScheduledStructure()
    {
        return $this->scheduledStructure;
    }

    /**
     * @return \Magento\Framework\View\Page\Config\Structure
     */
    public function getPageConfigStructure()
    {
        return $this->pageConfigStructure;
    }
}
