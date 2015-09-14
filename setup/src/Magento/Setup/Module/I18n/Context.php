<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;

/**
 *  Context
 */
class Context
{
    /**
     * Locale directory
     */
    const LOCALE_DIRECTORY = 'i18n';

    /**#@+
     * Context info
     */
    const CONTEXT_TYPE_MODULE = 'module';

    const CONTEXT_TYPE_THEME = 'theme';

    const CONTEXT_TYPE_LIB = 'lib';

    /**#@-*/

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Get context from file path in array(<context type>, <context value>) format
     * - for module: <Namespace>_<module name>
     * - for theme: <area>/<theme name>
     * - for pub: relative path to file
     *
     * @param string $path
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getContextByPath($path)
    {
        if ($value = $this->getModuleName($path)) {
            $type = self::CONTEXT_TYPE_MODULE;
        } elseif ($value = $this->getThemeName($path)) {
            $type = self::CONTEXT_TYPE_THEME;
        } elseif ($value = strstr($path, '/lib/web/')) {
            $type = self::CONTEXT_TYPE_LIB;
            $value = ltrim($value, '/');
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid path given: "%s".', $path));
        }
        return [$type, $value];
    }

    /**
     * Try to get module name by path, return false if not a module
     *
     * @param string $path
     * @return bool|string
     */
    private function getModuleName($path)
    {
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            if (strpos($path, $moduleDir) !== false) {
                return $moduleName;
            }
        }
        return false;
    }

    /**
     * Try to get theme name by path, return false if not a theme
     *
     * @param string $path
     * @return bool|string
     */
    private function getThemeName($path)
    {
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themeName => $themeDir) {
            if (strpos($path, $themeDir) !== false) {
                return $themeName;
            }
        }
        return false;
    }

    /**
     * Get paths by context
     *
     * @param string $type
     * @param array $value
     * @return string
     * @throws \InvalidArgumentException
     */
    public function buildPathToLocaleDirectoryByContext($type, $value)
    {
        switch ($type) {
            case self::CONTEXT_TYPE_MODULE:
                $absolutePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $value);
                $path = str_replace(BP . '/', '', $absolutePath);
                break;
            case self::CONTEXT_TYPE_THEME:
                $absolutePath = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $value);
                $path = str_replace(BP . '/', '', $absolutePath);
                break;
            case self::CONTEXT_TYPE_LIB:
                $path = 'lib/web';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid context given: "%s".', $type));
        }
        return $path . '/' . self::LOCALE_DIRECTORY . '/';
    }
}
