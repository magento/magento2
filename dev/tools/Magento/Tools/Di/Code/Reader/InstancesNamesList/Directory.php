<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Code\Reader\InstancesNamesList;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Tools\Di\Compiler\Log\Log;

/**
 * Class Directory
 *
 * @package Magento\Tools\Di\Code\Reader\InstancesNamesList
 */
class Directory implements \Magento\Tools\Di\Code\Reader\InstancesNamesList
{
    /**
     * @var string
     */
    private $current;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var array
     */
    private $relations = [];

    /**
     * @var \Magento\Framework\Code\Validator
     */
    private $validator;

    /**
     * @var \Magento\Tools\Di\Code\Reader\ClassReaderDecorator
     */
    private $classReader;

    /**
     * @var \Magento\Tools\Di\Code\Reader\ClassesScanner
     */
    private $classesScanner;

    /**
     * @param Log $log
     * @param     $generationDir
     */
    public function __construct(Log $log, $generationDir) {

        $this->classReader = new ClassReader();
        $this->classesScanner = new \Magento\Tools\Di\Code\Reader\ClassesScanner();

        $this->log = $log;
        $this->generationDir = $generationDir;

        $this->validator = new \Magento\Framework\Code\Validator();
        $this->validator->add(new \Magento\Framework\Code\Validator\ConstructorIntegrity());
        $this->validator->add(new \Magento\Framework\Code\Validator\ContextAggregation());

        set_error_handler([$this, 'errorHandler'], E_STRICT);
    }

    /**
     * ErrorHandler for logging
     *
     * @param int $errorNumber
     * @param string $msg
     *
     * @return void
     */
    public function errorHandler($errorNumber, $msg)
    {
        $this->log->add(Log::COMPILATION_ERROR, $this->current, '#'. $errorNumber .' '. $msg);
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
        foreach ($this->classesScanner->getList($path) as $className) {
            $this->current = $className; // for errorHandler function
            try {
                if ($path != $this->generationDir) { // validate all classes except classes in generation dir
                    $this->validator->validate($className);
                }
                $this->relations[$className] = $this->classReader->getParents($className);
            } catch (\Magento\Framework\Code\ValidationException $exception) {
                $this->log->add(Log::COMPILATION_ERROR, $className, $exception->getMessage());
            } catch (\ReflectionException $e) {
                $this->log->add(Log::COMPILATION_ERROR, $className, $e->getMessage());
            }
        }

        return $this->relations;
    }

    /**
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }
}