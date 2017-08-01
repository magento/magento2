<?php
/**
 * List of active application modules.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Interface \Magento\Framework\Module\ModuleListInterface
 *
 * @since 2.0.0
 */
interface ModuleListInterface
{
    /**
     * Get list of all modules
     *
     * Returns an array where key is module name and value is an array with module meta-information
     *
     * @return array
     * @since 2.0.0
     */
    public function getAll();

    /**
     * Get module declaration data
     *
     * Returns an array with meta-information about one module by specified name
     *
     * @param string $name
     * @return array|null
     * @since 2.0.0
     */
    public function getOne($name);

    /**
     * Enumerates the list of names of modules
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getNames();

    /**
     * Checks whether the specified module is present in the list
     *
     * @param string $name
     * @return bool
     * @since 2.0.0
     */
    public function has($name);
}
