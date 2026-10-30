<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Setup;

/**
 * Context of a module being installed/updated: version, user data, etc.
 * @api
 * @since 100.0.2
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
