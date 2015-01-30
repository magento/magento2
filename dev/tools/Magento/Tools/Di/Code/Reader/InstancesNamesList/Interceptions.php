<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Code\Reader\InstancesNamesList;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Tools\Di\Compiler\Log\Log;

/**
 * Class Interceptions
 *
 * @package Magento\Tools\Di\Code\Reader\InstancesNamesList
 */
class Interceptions implements \Magento\Tools\Di\Code\Reader\InstancesNamesList
{
    /**
     * @var \Magento\Tools\Di\Code\Reader\ClassReaderDecorator
     */
    private $classReader;

    /**
     * @var \Magento\Tools\Di\Code\Reader\ClassesScanner
     */
    private $classesScanner;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var \Magento\Framework\Code\Validator
     */
    private $validator;

    /**
     * @param \Magento\Tools\Di\Code\Reader\ClassesScanner $classesScanner
     * @param ClassReader                                  $classReader
     */
    public function __construct(
        \Magento\Tools\Di\Code\Reader\ClassesScanner $classesScanner,
        ClassReader $classReader
    ) {
        $this->classReader = $classReader;
        $this->classesScanner = $classesScanner;

        $this->log = new \Magento\Tools\Di\Compiler\Log\Log(
            new \Magento\Tools\Di\Compiler\Log\Writer\Quiet(),
            new \Magento\Tools\Di\Compiler\Log\Writer\Console()
        );

        $this->validator = new \Magento\Framework\Code\Validator();
        $this->validator->add(new \Magento\Framework\Code\Validator\ConstructorIntegrity());
        $this->validator->add(new \Magento\Framework\Code\Validator\ContextAggregation());
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
        $nameList = [];
        foreach ($this->classesScanner->getList($path) as $className) {
            try {
                if (!strpos($path, 'generation')) { // validate all classes except classes in var/generation dir
                    $this->validator->validate($className);
                }
                $nameList[] = $className;
            } catch (\Magento\Framework\Code\ValidationException $exception) {
                $this->log->add(Log::COMPILATION_ERROR, $className, $exception->getMessage());
            } catch (\ReflectionException $e) {
                $this->log->add(Log::COMPILATION_ERROR, $className, $e->getMessage());
            }
        }

        $this->log->report();

        return $nameList;
    }
}