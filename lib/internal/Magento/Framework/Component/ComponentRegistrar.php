<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * Available component types.
     */
    const MODULE = 'module';
    const LIBRARY = 'library';
    const THEME = 'theme';
    const LANGUAGE = 'language';
    const SETUP = 'setup';
    /**#@- */

    /**#@- */
    private static $paths = [
        self::MODULE => [],
        self::LIBRARY => [],
        self::LANGUAGE => [],
        self::THEME => [],
        self::SETUP => []
    ];

    /**
     * Register a component.
     *
     * @param string $type component type
     * @param string $componentName Fully-qualified component name
     * @param string $path Absolute file path to the component
     * @throws \LogicException
     * @return void
     */
    public static function register(string $type, string $componentName, string $path): void
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
     * Register a module component.
     *
     * @param string $componentName
     * @param string $path
     * @return void
     */
    public static function registerModule(string $componentName, string $path): void
    {
        self::register(self::MODULE, $componentName, $path);
    }

    /**
     * Register a library component.
     *
     * @param string $componentName
     * @param string $path
     * @return void
     */
    public static function registerLibrary(string $componentName, string $path): void
    {
        self::register(self::LIBRARY, $componentName, $path);
    }

    /**
     * Register a theme component.
     *
     * @param string $componentName
     * @param string $path
     * @return void
     */
    public static function registerTheme(string $componentName, string $path): void
    {
        self::register(self::THEME, $componentName, $path);
    }

    /**
     * Register a language component.
     *
     * @param string $componentName
     * @param string $path
     * @return void
     */
    public static function registerLanguage(string $componentName, string $path): void
    {
        self::register(self::LANGUAGE, $componentName, $path);
    }

    /**
     * Register a setup component.
     *
     * @param string $componentName
     * @param string $path
     * @return void
     */
    public static function registerSetup(string $componentName, string $path): void
    {
        self::register(self::SETUP, $componentName, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths(string $type): array
    {
        self::validateType($type);

        return self::$paths[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(string $type, string $componentName): ?string
    {
        self::validateType($type);

        return self::$paths[$type][$componentName] ?? null;
    }

    /**
     * Check if component type is valid.
     *
     * @param string $type
     * @return void
     * @throws \LogicException
     */
    private static function validateType(string $type): void
    {
        if (!isset(self::$paths[$type])) {
            throw new \LogicException(sprintf("'%s' is not a valid component type", $type));
        }
    }
}
