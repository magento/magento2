<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Service\V1;

/**
 * Module service.
 * @since 2.0.0
 */
class ModuleService implements ModuleServiceInterface
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     * @since 2.0.0
     */
    protected $moduleList;

    /**
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getModules()
    {
        return $this->moduleList->getNames();
    }
}
