<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

/**
 * Interface for an autoloader class that allows the dynamic modification of PSR-0 and PSR-4 mappings
 */
interface AutoloaderInterface
{
    /**
     * Adds a PSR-4 mapping from a namespace prefix to directories to search in for the corresponding class
     *
     * @param string $nsPrefix The namespace prefix of the PSR-4 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @param bool $prepend Whether to append the given path or paths to the paths already associated with the prefix
     * @return void
     */
    public function addPsr4($nsPrefix, $paths, $prepend = false);

    /**
     * Adds a PSR-0 mapping from a namespace prefix to directories to search in for the corresponding class
     *
     * @param string $nsPrefix The namespace prefix of the PSR-0 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @param bool $prepend Whether to append the given path or paths to the paths already associated with the prefix
     * @return void
     */
    public function addPsr0($nsPrefix, $paths, $prepend = false);

    /**
     * Creates new PSR-0 mappings from the given prefix to the given set of paths, eliminating previous mappings
     *
     * @param string $nsPrefix The namespace prefix of the PSR-0 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @return void
     */
    public function setPsr0($nsPrefix, $paths);

    /**
     * Creates new PSR-4 mappings from the given prefix to the given set of paths, eliminating previous mappings
     *
     * @param string $nsPrefix The namespace prefix of the PSR-0 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @return void
     */
    public function setPsr4($nsPrefix, $paths);

    /**
     * Attempts to load a class and returns true if successful.
     *
     * @param string $className
     * @return bool
     */
    public function loadClass($className);

    /**
     * Get filepath of class on system or false if it does not exist
     *
     * @param string $className
     * @return string|bool
     */
    public function findFile($className);
}
