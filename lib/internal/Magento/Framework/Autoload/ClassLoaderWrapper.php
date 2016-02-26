<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

use Composer\Autoload\ClassLoader;

/**
 * Decorator for the autoloader class provided by Composer
 *
 * The decorator is necessary to decorate findFile()
 *
 * @see ClassLoaderWrapper::findFile()
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
     * Constructor
     *
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
     * Decorated findFile()
     *
     * Composer remembers that files don't exist even after they are generated. This clears the entry for
     * $className so we can check the filesystem again for class existence.
     *
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function findFile($className)
    {
        /**
         * $className must be FQCN which does not start with a backslash.
         */
        if ($className[0] === '\\') {
            trigger_error($message = 'Invalid FQCN: ' . $className, E_USER_ERROR);
            throw new \RuntimeException($message);
        }

        $classMap = $this->autoloader->getClassMap();
        $wasNotFoundPreviously = isset($classMap[$className]) && false === $classMap[$className];
        unset($classMap);
        if ($wasNotFoundPreviously) {
            $this->autoloader->addClassMap([$className => null]);
        }
        return $this->autoloader->findFile($className);
    }
}
