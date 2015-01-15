<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

use Composer\Autoload\ClassLoader;

/**
 * Wrapper designed to insulate the autoloader class provided by Composer
 */
class ClassLoaderWrapper implements AutoloaderInterface
{
    /**
     * Using the autoloader class provided by Composer
     *
     * @var ClassLoader
     */
    protected $autoloader;

    /**
     * @param ClassLoader $autoloader
     */
    public function __construct(ClassLoader $autoloader)
    {
        $this->autoloader = $autoloader;
    }

    /**
     * Adds a PSR-4 mapping from a namespace prefix to directories to search in for the corresponding class
     *
     * @param string $nsPrefix The namespace prefix of the PSR-4 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @param bool $prepend Whether to append the given path or paths to the paths already associated with the prefix
     * @return void
     */
    public function addPsr4($nsPrefix, $paths, $prepend = false)
    {
        $this->autoloader->addPsr4($nsPrefix, $paths, $prepend);
    }

    /**
     * Adds a PSR-0 mapping from a namespace prefix to directories to search in for the corresponding class
     *
     * @param string $nsPrefix The namespace prefix of the PSR-0 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @param bool $prepend Whether to append the given path or paths to the paths already associated with the prefix
     * @return void
     */
    public function addPsr0($nsPrefix, $paths, $prepend = false)
    {
        $this->autoloader->add($nsPrefix, $paths, $prepend);
    }

    /**
     * Creates new PSR-0 mappings from the given prefix to the given set of paths, eliminating previous mappings
     *
     * @param string $nsPrefix The namespace prefix of the PSR-0 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @return void
     */
    public function setPsr0($nsPrefix, $paths)
    {
        $this->autoloader->set($nsPrefix, $paths);
    }

    /**
     * Creates new PSR-4 mappings from the given prefix to the given set of paths, eliminating previous mappings
     *
     * @param string $nsPrefix The namespace prefix of the PSR-0 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @return void
     */
    public function setPsr4($nsPrefix, $paths)
    {
        $this->autoloader->setPsr4($nsPrefix, $paths);
    }

    /**
     * Attempts to load a class and returns true if successful.
     *
     * @param string $className
     * @return bool
     */
    public function loadClass($className)
    {
        return $this->autoloader->loadClass($className) === true;
    }
}
