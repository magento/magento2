<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Di\Code\Reader\Decorator;

use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Framework\Exception\FileSystemException;

class Area implements \Magento\Setup\Module\Di\Code\Reader\ClassesScannerInterface
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
     * @throws FileSystemException
     */
    public function getList($path)
    {
        $classes = [];
        foreach ($this->classesScanner->getList($path) as $className) {
            $classes[$className] = (array) $this->classReaderDecorator->getConstructor($className);
        }

        return $classes;
    }
}
