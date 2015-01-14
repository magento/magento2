<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Compiler;

use Magento\Tools\Di\Compiler\Log\Log;
use Zend\Code\Scanner\FileScanner;

class Directory
{
    /**
     * @var array
     */
    protected $_processedClasses = [];

    /**
     * @var array
     */
    protected $_definitions = [];

    /**
     * @var string
     */
    protected $_current;

    /**
     * @var Log
     */
    protected $_log;

    /**
     * @var array
     */
    protected $_relations;

    /**
     * @var  \Magento\Framework\Code\Validator
     */
    protected $_validator;

    /**
     * @param Log $log
     * @param \Magento\Framework\Code\Validator $validator
     */
    public function __construct(Log $log, \Magento\Framework\Code\Validator $validator)
    {
        $this->_log = $log;
        $this->_validator = $validator;
        set_error_handler([$this, 'errorHandler'], E_STRICT);
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function errorHandler($errno, $errstr)
    {
        $this->_log->add(Log::COMPILATION_ERROR, $this->_current, $errstr);
    }

    /**
     * Compile class definitions
     *
     * @param string $path
     * @param bool $validate
     * @return void
     */
    public function compile($path, $validate = true)
    {
        $rdi = new \RecursiveDirectoryIterator(realpath($path));
        $recursiveIterator = new \RecursiveIteratorIterator($rdi, 1);
        /** @var $item \SplFileInfo */
        foreach ($recursiveIterator as $item) {
            if ($item->isFile() && pathinfo($item->getRealPath(), PATHINFO_EXTENSION) == 'php') {
                $fileScanner = new FileScanner($item->getRealPath());
                $classNames = $fileScanner->getClassNames();
                foreach ($classNames as $className) {
                    $this->_current = $className;
                    if (!class_exists($className)) {
                        require_once $item->getRealPath();
                    }
                    try {
                        if ($validate) {
                            $this->_validator->validate($className);
                        }
                        $signatureReader = new \Magento\Framework\Code\Reader\ClassReader();
                        $this->_definitions[$className] = $signatureReader->getConstructor($className);
                        $this->_relations[$className] = $signatureReader->getParents($className);
                    } catch (\Magento\Framework\Code\ValidationException $exception) {
                        $this->_log->add(Log::COMPILATION_ERROR, $className, $exception->getMessage());
                    } catch (\ReflectionException $e) {
                        $this->_log->add(Log::COMPILATION_ERROR, $className, $e->getMessage());
                    }
                    $this->_processedClasses[$className] = 1;
                }
            }
        }
    }

    /**
     * Retrieve compilation result
     *
     * @return array
     */
    public function getResult()
    {
        return [$this->_definitions, $this->_relations];
    }
}
