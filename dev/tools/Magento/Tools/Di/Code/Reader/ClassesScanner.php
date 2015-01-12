<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\Di\Code\Reader;

use Magento\Framework\Filesystem\FilesystemException;
use Zend\Code\Scanner\FileScanner;

class ClassesScanner
{
    /**
     * @var ClassReaderDecorator
     */
    private $classReaderDecorator;

    /**
     * @param ClassReaderDecorator $classReaderDecorator
     */
    public function __construct(ClassReaderDecorator $classReaderDecorator)
    {
        $this->classReaderDecorator = $classReaderDecorator;
    }

    /**
     * Retrieves list of classes and arguments for given path
     * [CLASS NAME => ConstructorArgument[]]
     *
     * @param string $path
     * @return array
     * @throws FilesystemException
     */
    public function getList($path)
    {
        $realPath = realpath($path);
        if (!(bool)$realPath) {
            throw new FilesystemException();
        }
        $classes = [];
        $recursiveIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($realPath),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        /** @var $fileItem \SplFileInfo */
        foreach ($recursiveIterator as $fileItem) {
            if (!$this->isPhpFile($fileItem)) {
                continue;
            }
            $fileScanner = new FileScanner($fileItem->getRealPath());
            $classNames = $fileScanner->getClassNames();
            foreach ($classNames as $className) {
                if (!class_exists($className)) {
                    require_once $fileItem->getRealPath();
                }
                $classes[$className] =  $this->classReaderDecorator->getConstructor($className);
            }
        }
        return $classes;
    }

    /**
     * Whether file is .php file
     *
     * @param \SplFileInfo $item
     * @return bool
     */
    private function isPhpFile(\SplFileInfo $item)
    {
        return $item->isFile() && pathinfo($item->getRealPath(), PATHINFO_EXTENSION) == 'php';
    }
}
