<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Reader\Decorator;

use Magento\Setup\Module\Di\Compiler\Log\Log;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Interceptions
 *
 * @package Magento\Setup\Module\Di\Code\Reader\Decorator
 */
class Interceptions implements \Magento\Setup\Module\Di\Code\Reader\ClassesScannerInterface
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator
     */
    private $classReader;

    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassesScanner
     */
    private $classesScanner;

    /**
     * @var \Magento\Setup\Module\Di\Compiler\Log\Log
     */
    private $log;

    /**
     * @var \Magento\Framework\Code\Validator
     */
    private $validator;

    /**
     * @param \Magento\Setup\Module\Di\Code\Reader\ClassesScanner $classesScanner
     * @param \Magento\Framework\Code\Reader\ClassReader $classReader
     * @param \Magento\Framework\Code\Validator $validator
     * @param \Magento\Framework\Code\Validator\ConstructorIntegrity $constructorIntegrityValidator
     * @param Log $log
     */
    public function __construct(
        \Magento\Setup\Module\Di\Code\Reader\ClassesScanner $classesScanner,
        \Magento\Framework\Code\Reader\ClassReader $classReader,
        \Magento\Framework\Code\Validator $validator,
        \Magento\Framework\Code\Validator\ConstructorIntegrity $constructorIntegrityValidator,
        Log $log
    ) {
        $this->classReader = $classReader;
        $this->classesScanner = $classesScanner;
        $this->validator = $validator;
        $this->log = $log;

        $this->validator->add($constructorIntegrityValidator);
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
        $nameList = [];
        foreach ($this->classesScanner->getList($path) as $className) {
            try {
                // validate all classes except classes in generated/code dir
                $generatedCodeDir = DirectoryList::getDefaultConfig()[DirectoryList::GENERATED_CODE];
                if (strpos($path, $generatedCodeDir[DirectoryList::PATH]) === false) {
                    $this->validator->validate($className);
                }
                $nameList[] = $className;
            } catch (\Magento\Framework\Exception\ValidatorException $exception) {
                $this->log->add(Log::COMPILATION_ERROR, $className, $exception->getMessage());
            } catch (\ReflectionException $e) {
                $this->log->add(Log::COMPILATION_ERROR, $className, $e->getMessage());
            }
        }

        $this->log->report();

        return $nameList;
    }
}
