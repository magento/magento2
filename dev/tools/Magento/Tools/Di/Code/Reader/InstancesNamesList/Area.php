<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Tools\Di\Code\Reader\InstancesNamesList;

class Area implements \Magento\Tools\Di\Code\Reader\InstancesNamesList
{
    /**
     * @var \Magento\Tools\Di\Code\Reader\ClassReaderDecorator
     */
    private $classReaderDecorator;

    /**
     * @var \Magento\Tools\Di\Code\Reader\ClassesScanner
     */
    private $classesScanner;

    /**
     * @param \Magento\Tools\Di\Code\Reader\ClassesScanner       $classesScanner
     * @param \Magento\Tools\Di\Code\Reader\ClassReaderDecorator $classReaderDecorator
     */
    public function __construct(
        \Magento\Tools\Di\Code\Reader\ClassesScanner $classesScanner,
        \Magento\Tools\Di\Code\Reader\ClassReaderDecorator $classReaderDecorator
    ) {
        $this->classReaderDecorator = $classReaderDecorator;
        $this->classesScanner = $classesScanner;
    }

    /**
     * Retrieves list of classes for given path
     *
     * @param $path
     *
     * @return array
     *
     * @throws \Magento\Framework\Filesystem\FilesystemException
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