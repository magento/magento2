<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

/**
 * Encapsulates directories structure of a Magento module
 */
class Dir
{
    /**#@+
     * Directories within modules
     */
    const MODULE_ETC_DIR = 'etc';
    const MODULE_I18N_DIR = 'i18n';
    const MODULE_VIEW_DIR = 'view';
    const MODULE_CONTROLLER_DIR = 'Controller';
    const MODULE_SETUP_DIR = 'Setup';
    /**#@-*/

    private const ALLOWED_DIR_TYPES = [
        self::MODULE_ETC_DIR => true,
        self::MODULE_I18N_DIR => true,
        self::MODULE_VIEW_DIR => true,
        self::MODULE_CONTROLLER_DIR => true,
        self::MODULE_SETUP_DIR => true
    ];

    /**#@-*/
    private $componentRegistrar;

    /**
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Retrieve full path to a directory of certain type within a module
     *
     * @param string $moduleName Fully-qualified module name
     * @param string $type Type of module's directory to retrieve
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getDir($moduleName, $type = '')
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);

        // An empty $type means it's getting the directory of the module itself.
        if (empty($type) && !isset($path)) {
            // Note: do not throw \LogicException, as it would break backwards-compatibility.
            throw new \InvalidArgumentException("Module '$moduleName' is not correctly registered.");
        }

        if ($type) {
            if (!isset(self::ALLOWED_DIR_TYPES[$type])) {
                throw new \InvalidArgumentException("Directory type '{$type}' is not recognized.");
            }
            $path .= '/' . $type;
        }

        return $path;
    }
}
