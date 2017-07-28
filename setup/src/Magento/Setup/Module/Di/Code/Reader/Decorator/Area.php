<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Reader\Decorator;

use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class Area
 *
 * @package Magento\Setup\Module\Di\Code\Reader\Decorator
 * @since 2.0.0
 */
class Area implements \Magento\Setup\Module\Di\Code\Reader\ClassesScannerInterface
{
    /**
     * @var ClassReaderDecorator
     * @since 2.0.0
     */
    private $classReaderDecorator;

    /**
     * @var ClassesScanner
     * @since 2.0.0
     */
    private $classesScanner;

    /**
     * @param ClassesScanner $classesScanner
     * @param ClassReaderDecorator $classReaderDecorator
     * @since 2.0.0
     */
    public function __construct(
        ClassesScanner $classesScanner,
        ClassReaderDecorator $classReaderDecorator
    ) {
        $this->classReaderDecorator = $classReaderDecorator;
        $this->classesScanner = $classesScanner;
    }

    /**
     * Retrieves list of classes for given path
     *
     * @param string $path path to dir with files
     *
     * @return array
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function getList($path)
    {
        $classes = [];
        foreach ($this->classesScanner->getList($path) as $className) {
            $classes[$className] = $this->classReaderDecorator->getConstructor($className);
        }

        return $classes;
    }
}
