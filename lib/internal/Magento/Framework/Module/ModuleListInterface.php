<?php
/**
 * List of active application modules.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
