<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Service\V1;

/**
 * Module service.
 */
class ModuleService implements ModuleServiceInterface
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Returns an array of enabled modules
     *
     * @return string[]
     */
    public function getModules()
    {
        return $this->moduleList->getNames();
    }
}
