<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;

/**
 *  Context
 * @since 2.0.0
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
     * @since 2.0.0
     */
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param ComponentRegistrar $componentRegistrar
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getContextByPath($path)
    {
        if ($value = $this->getComponentName(ComponentRegistrar::MODULE, $path)) {
            $type = self::CONTEXT_TYPE_MODULE;
        } elseif ($value = $this->getComponentName(ComponentRegistrar::THEME, $path)) {
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
     * Try to get component name by path, return false if not found
     *
     * @param string $componentType
     * @param string $path
     * @return bool|string
     * @since 2.0.0
     */
    private function getComponentName($componentType, $path)
    {
        foreach ($this->componentRegistrar->getPaths($componentType) as $componentName => $componentDir) {
            $componentDir .= '/';
            if (strpos($path, $componentDir) !== false) {
                return $componentName;
            }
        }
        return false;
    }

    /**
     * Get paths by context
     *
     * @param string $type
     * @param array $value
     * @return string|null
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function buildPathToLocaleDirectoryByContext($type, $value)
    {
        switch ($type) {
            case self::CONTEXT_TYPE_MODULE:
                $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $value);
                break;
            case self::CONTEXT_TYPE_THEME:
                $path = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $value);
                break;
            case self::CONTEXT_TYPE_LIB:
                $path = BP . '/lib/web';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid context given: "%s".', $type));
        }

        return (null === $path) ? null : $path . '/' . self::LOCALE_DIRECTORY . '/';
    }
}
