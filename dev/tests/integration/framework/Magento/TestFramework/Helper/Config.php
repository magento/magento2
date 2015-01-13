<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Helper with routines to work with Magento config
 */
namespace Magento\TestFramework\Helper;

class Config
{
    /**
     * Returns enabled modules in the system
     *
     * @return array
     */
    public function getEnabledModules()
    {
        /** @var \Magento\Framework\Module\ModuleListInterface $moduleList */
        $moduleList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Module\ModuleListInterface'
        );
        return $moduleList->getNames();
    }
}
