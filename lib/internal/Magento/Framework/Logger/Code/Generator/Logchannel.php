<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Code\Generator;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Code\Generator\CodeGeneratorInterface;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Logger\Monolog;
use \Magento\Framework\Logger\Logchannel as LogchannelBaseClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Logchannel Code Generator
 *
 * Responsible for generating ...Logchannel classed automatically.
 *
 * Classes are instantiated from \Magento\Framework\Logger\Monolog when there is no specific configuration (For BC)
 * Classed are instantiated from \Magento\Framework\Logger\Logchannel when there is channel specific configuration
 */
class Logchannel
{
    /**
     * @var string[]
     */
    private $_errors = [];

    /**
     * Source model class name
     *
     * @var string
     */
    private $sourceClassName;

    /**
     * Result model class name
     *
     * @var string
     */
    private $resultClassName;

    /**
     * @var string
     */
    private $channelName;

    /**
     * @var Io
     */
    private $ioObject;

    /**
     * @var CodeGeneratorInterface
     */
    private $classGenerator;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param null|string $sourceClassName
     * @param null|string $resultClassName
     * @param null|Io $ioObject
     * @param null|CodeGeneratorInterface $classGenerator
     */
    public function __construct(
        $sourceClassName,
        $resultClassName,
        Io $ioObject = null,
        CodeGeneratorInterface $classGenerator = null
    ) {
        $this->sourceClassName = $this->getFullyQualifiedClassName($sourceClassName);
        $this->resultClassName = $this->getFullyQualifiedClassName($resultClassName);
        $this->ioObject = $ioObject !== null ? $ioObject : new Io(new File());
        $this->classGenerator = $classGenerator !== null ? $classGenerator : new ClassGenerator();
        $this->deploymentConfig = ObjectManager::getInstance()->get(DeploymentConfig::class);

        $this->channelName = strtolower(preg_replace(
            '/([A-Z]+)/',
            '-$1',
            lcfirst(substr($sourceClassName, strrpos($sourceClassName, '\\') + 1))
        ));
    }

    /**
     * Generation template method
     *
     * @return bool
     */
    public function generate()
    {
        try {
            $sourceCode = $this->generateCode();
            $fileName = $this->ioObject->generateResultFileName($this->resultClassName);
            $this->ioObject->makeResultFileDirectory($this->resultClassName);
            $this->ioObject->writeResultFile($fileName, $sourceCode);

            return $fileName;
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }
        return false;
    }

    /**
     * Get full source class name, with namespace
     *
     * @return string
     */
    public function getSourceClassName()
    {
        return class_exists($this->sourceClassName) ? $this->sourceClassName : Monolog::class;
    }

    /**
     * List of occurred generation errors
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->_errors;
    }

    /**
     * Generate code
     *
     * @return string
     */
    private function generateCode(): string
    {
        $logChannelConfig = $this->deploymentConfig->get('logging');

        $this->classGenerator->setName($this->resultClassName);

        if ($logChannelConfig === null ||
            !isset($logChannelConfig['channels']) ||
            !isset($logChannelConfig['channels'][$this->channelName])
        ) {
            return $this->classGenerator
                ->setExtendedClass(Monolog::class)
                ->generate();
        }

        $this->classGenerator
            ->setExtendedClass(
                class_exists($this->sourceClassName) ? $this->sourceClassName : LogchannelBaseClass::class
            )
            ->addProperty(
                'channelName',
                $this->channelName,
                PropertyGenerator::FLAG_PROTECTED
            )
            ->addProperty(
                'channelConfiguration',
                $logChannelConfig['channels'][$this->channelName],
                PropertyGenerator::FLAG_PROTECTED
            );

        return $this->classGenerator->generate();
    }

    /**
     * Add error message
     *
     * @param string $message
     */
    private function addError(string $message)
    {
        $this->_errors[] = $message;
    }

    /**
     * Get fully qualified class name
     *
     * @param string $className
     * @return string
     */
    private function getFullyQualifiedClassName($className)
    {
        $className = ltrim($className, '\\');
        return $className ? '\\' . $className : '';
    }
}
