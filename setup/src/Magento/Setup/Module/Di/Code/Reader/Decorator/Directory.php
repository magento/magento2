<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Reader\Decorator;

use Magento\Setup\Module\Di\Compiler\Log\Log;

/**
 * Class Directory
 *
 * @package Magento\Setup\Module\Di\Code\Reader\Decorator
 */
class Directory implements \Magento\Setup\Module\Di\Code\Reader\ClassesScannerInterface
{
    /**
     * @var string
     */
    private $current;

    /**
     * @var \Magento\Setup\Module\Di\Compiler\Log\Log
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
     * @var \Magento\Framework\Code\Reader\ClassReader
     */
    private $classReader;

    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassesScanner
     */
    private $classesScanner;

    /**
     * @var string
     */
    private $generationDir;

    /**
     * @param \Magento\Setup\Module\Di\Compiler\Log\Log $log Logging object
     * @param \Magento\Framework\Code\Reader\ClassReader $classReader
     * @param \Magento\Setup\Module\Di\Code\Reader\ClassesScanner $classesScanner
     * @param \Magento\Framework\Code\Validator $validator
     * @param string $generationDir directory where generated files is
     */
    public function __construct(
        \Magento\Setup\Module\Di\Compiler\Log\Log $log,
        \Magento\Framework\Code\Reader\ClassReader $classReader,
        \Magento\Setup\Module\Di\Code\Reader\ClassesScanner $classesScanner,
        \Magento\Framework\Code\Validator $validator,
        $generationDir
    ) {
        $this->log = $log;
        $this->classReader = $classReader;
        $this->classesScanner = $classesScanner;
        $this->validator = $validator;
        $this->generationDir = $generationDir;

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
        $this->log->add(Log::COMPILATION_ERROR, $this->current, '#' . $errorNumber . ' ' . $msg);
    }

    /**
     * Retrieves list of classes for given path
     *
     * @param string $path path to dir with files
     *
     * @return array
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
            } catch (\Magento\Framework\Exception\ValidatorException $exception) {
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
