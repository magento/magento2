<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register language
 */
class LanguageRegistrar implements ComponentRegistryInterface
{
    /**
     * Paths to language
     *
     * @var string[]
     */
    private static $languagePaths = [];

    /**
     * Sets the location of a themes.
     *
     * @param string $languageName Language name
     * @param string $path Absolute file path to the language
     * @return void
     */
    public static function register($languageName, $path)
    {
        self::$languagePaths[$languageName] = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        return self::$languagePaths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($languageName)
    {
        return isset(self::$languagePaths[$languageName]) ? self::$languagePaths[$languageName] : null;
    }
}
