<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * {@inheritdoc}
     */
    public function addPsr4($nsPrefix, $paths, $prepend = false)
    {
        $this->autoloader->addPsr4($nsPrefix, $paths, $prepend);
    }

    /**
     * {@inheritdoc}
     */
    public function addPsr0($nsPrefix, $paths, $prepend = false)
    {
        $this->autoloader->add($nsPrefix, $paths, $prepend);
    }

    /**
     * {@inheritdoc}
     */
    public function setPsr0($nsPrefix, $paths)
    {
        $this->autoloader->set($nsPrefix, $paths);
    }

    /**
     * {@inheritdoc}
     */
    public function setPsr4($nsPrefix, $paths)
    {
        $this->autoloader->setPsr4($nsPrefix, $paths);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function loadClass($className)
    {
        return $this->autoloader->loadClass($className) === true;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function findFile($className)
    {
        /**
         * Composer remembers that files don't exist even after they are generated. This clears the entry for
         * $className so we can check the filesystem again for class existence.
         */
        if ($className[0] === '\\') {
            $className = substr($className, 1);
        }
        $this->autoloader->addClassMap([$className => null]);
        return $this->autoloader->findFile($className);
    }
}
