<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register components.
 *
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 *
 * @api
 */
class ComponentRegistrar implements ComponentRegistrarInterface
{
    /**#@+
     * Different types of components
     */
    const MODULE = 'module';
    const LIBRARY = 'library';
    const THEME = 'theme';
    const LANGUAGE = 'language';
    /**#@- */

    /**
     * All paths
     *
     * @var array
     */
    private static $paths = [
        self::MODULE => [],
        self::LIBRARY => [],
        self::LANGUAGE => [],
        self::THEME => [],
    ];

    /**
     * Sets the location of a component.
     *
     * @param string $type component type
     * @param string $componentName Fully-qualified component name
     * @param string $path Absolute file path to the component
     * @throws \LogicException
     * @return void
     */
    public static function register($type, $componentName, $path)
    {
        self::validateType($type);
        if (isset(self::$paths[$type][$componentName])) {
            throw new \LogicException(
                ucfirst($type) . ' \'' . $componentName . '\' from \'' . $path . '\' '
                . 'has been already defined in \'' . self::$paths[$type][$componentName] . '\'.'
            );
        } else {
            self::$paths[$type][$componentName] = str_replace('\\', '/', $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths($type)
    {
        self::validateType($type);
        return self::$paths[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($type, $componentName)
    {
        self::validateType($type);
        return isset(self::$paths[$type][$componentName]) ? self::$paths[$type][$componentName] : null;
    }

    /**
     * Checks if type of component is valid
     *
     * @param string $type
     * @return void
     * @throws \LogicException
     */
    private static function validateType($type)
    {
        if (!isset(self::$paths[$type])) {
            throw new \LogicException('\'' . $type . '\' is not a valid component type');
        }
    }
}
