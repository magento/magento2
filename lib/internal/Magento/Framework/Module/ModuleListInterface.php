<?php
/**
 * List of active application modules.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

interface ModuleListInterface
{
    /**
     * Get list of all modules
     *
     * Returns an array where key is module name and value is an array with module meta-information
     *
     * @return array
     */
    public function getAll();

    /**
     * Get module declaration data
     *
     * Returns an array with meta-information about one module by specified name
     *
     * @param string $name
     * @return array|null
     */
    public function getOne($name);

    /**
     * Enumerates the list of names of modules
     *
     * @return string[]
     */
    public function getNames();

    /**
     * Checks whether the specified module is present in the list
     *
     * @param string $name
     * @return bool
     */
    public function has($name);
}
