<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register components.
 *
 * @author Josh Di Fabio <joshdifabio@gmail.com>
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
     * Paths to modules
     *
     * @var string[]
     */
    protected static $modulePaths;

    /**
     * Paths to themes
     *
     * @var string[]
     */
    protected static $themePaths;

    /**
     * Paths to language
     *
     * @var string[]
     */
    protected static $languagePaths;

    /**
     * Paths to library
     *
     * @var string[]
     */
    protected static $libraryPaths;

    /**
     * Type of registrar
     *
     * @var string
     */
    protected $type;

    /**
     * Constructor
     *
     * @param string $type
     * @throws \LogicException
     */
    public function __construct($type)
    {
        if ($type === self::LANGUAGE || $type === self::MODULE || $type === self::LIBRARY || $type === self::THEME) {
            $this->type = $type;
        } else {
            throw new \LogicException('\'' . $type . '\' is not a valid component type');
        }
    }

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
        switch ($type){
            case self::MODULE :
                if (isset(self::$modulePaths[$componentName])) {
                    throw new \LogicException('\'' . $componentName . '\' module already exists');
                } else {
                    self::$modulePaths[$componentName] = $path;
                }
                break;
            case self::LIBRARY :
                if (isset(self::$libraryPaths[$componentName])) {
                    throw new \LogicException('\'' . $componentName . '\' library already exists');
                } else {
                    self::$libraryPaths[$componentName] = $path;
                }
                break;
            case self::THEME :
                if (isset(self::$themePaths[$componentName])) {
                    throw new \LogicException('\'' . $componentName . '\' theme already exists');
                } else {
                    self::$themePaths[$componentName] = $path;
                }
                break;
            case self::LANGUAGE :
                if (isset(self::$languagePaths[$componentName])) {
                    throw new \LogicException('\'' . $componentName . '\' language already exists');
                } else {
                    self::$languagePaths[$componentName] = $path;
                }
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        switch ($this->type){
            case self::MODULE :
                return self::$modulePaths;
            case self::LIBRARY :
                return self::$libraryPaths;
            case self::THEME :
                return self::$themePaths;
            case self::LANGUAGE :
                return self::$languagePaths;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($componentName)
    {
        switch ($this->type){
            case self::MODULE :
                return isset(self::$modulePaths[$componentName]) ? self::$modulePaths[$componentName] : null;
            case self::LIBRARY :
                return isset(self::$libraryPaths[$componentName]) ? self::$libraryPaths[$componentName] : null;
            case self::THEME :
                return isset(self::$themePaths[$componentName]) ? self::$themePaths[$componentName] : null;
            case self::LANGUAGE :
                return isset(self::$languagePaths[$componentName]) ? self::$languagePaths[$componentName] : null;
        }
    }
}
