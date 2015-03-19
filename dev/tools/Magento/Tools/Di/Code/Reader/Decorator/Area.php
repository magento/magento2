<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Code\Reader\Decorator;

use Magento\Tools\Di\Code\Reader\ClassesScanner;
use Magento\Tools\Di\Code\Reader\ClassReaderDecorator;
use Magento\Framework\Filesystem\FilesystemException;

/**
 * Class Area
 *
 * @package Magento\Tools\Di\Code\Reader\Decorator
 */
class Area implements \Magento\Tools\Di\Code\Reader\ClassesScannerInterface
{
    /**
     * @var ClassReaderDecorator
     */
    private $classReaderDecorator;

    /**
     * @var ClassesScanner
     */
    private $classesScanner;

    /**
     * @param ClassesScanner $classesScanner
     * @param ClassReaderDecorator $classReaderDecorator
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
     * @throws FilesystemException
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
