<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n;

use Magento\Framework\Component\ComponentRegistrarInterface;
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
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar)
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
        if ($value = $this->getComponentName(ComponentRegistrarInterface::MODULE, $path)) {
            $type = self::CONTEXT_TYPE_MODULE;
        } elseif ($value = $this->getComponentName(ComponentRegistrarInterface::THEME, $path)) {
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
     * @return string
     * @throws \InvalidArgumentException
     */
    public function buildPathToLocaleDirectoryByContext($type, $value)
    {
        switch ($type) {
            case self::CONTEXT_TYPE_MODULE:
                $absolutePath = $this->componentRegistrar->getPath(ComponentRegistrarInterface::MODULE, $value);
                $path = str_replace(BP . '/', '', $absolutePath);
                break;
            case self::CONTEXT_TYPE_THEME:
                $absolutePath = $this->componentRegistrar->getPath(ComponentRegistrarInterface::THEME, $value);
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
