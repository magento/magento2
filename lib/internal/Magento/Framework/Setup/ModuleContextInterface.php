<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Context of a module being installed/updated: version, user data, etc.
 */
interface ModuleContextInterface
{
    /**
     * Gets current version of the module
     *
     * @return string
     */
    public function getVersion();
}
