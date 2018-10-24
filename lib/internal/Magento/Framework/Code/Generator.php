<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

class Generator
{
    const GENERATION_SUCCESS = 'success';

    const GENERATION_ERROR = 'error';

    const GENERATION_SKIP = 'skip';

    /**
     * @var Io
     */
    protected $_ioObject;

    /**
     * @var array
     */
    protected $_generatedEntities;

    /**
     * @var DefinedClasses
     */
    protected $definedClasses;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Generator\Io $ioObject
     * @param array $generatedEntities
     * @param DefinedClasses $definedClasses
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Io $ioObject = null,
        array $generatedEntities = [],
        DefinedClasses $definedClasses = null,
        LoggerInterface $logger = null
    ) {
        $this->_ioObject = $ioObject ?: new Io(new File());
        $this->definedClasses = $definedClasses ?: new DefinedClasses();
        $this->_generatedEntities = $generatedEntities;
        $this->logger = $logger;
    }

    /**
     * Get generated entities
     *
     * @return array
     */
    public function getGeneratedEntities()
    {
        return $this->_generatedEntities;
    }

    /**
     * Set entity-to-generator map
     *
     * @param array $generatedEntities
     * @return $this
     */
    public function setGeneratedEntities($generatedEntities)
    {
        $this->_generatedEntities = $generatedEntities;
        return $this;
    }

    /**
     * Generate Class
     *
     * @param string $className
     * @return string | void
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function generateClass($className)
    {
        $resultEntityType = null;
        $sourceClassName = null;
        foreach ($this->_generatedEntities as $entityType => $generatorClass) {
            $entitySuffix = ucfirst($entityType);
            // If $className string ends with $entitySuffix substring
            if (strrpos($className, $entitySuffix) === strlen($className) - strlen($entitySuffix)) {
                $resultEntityType = $entityType;
                $sourceClassName = rtrim(
                    substr($className, 0, -1 * strlen($entitySuffix)),
                    '\\'
                );
                break;
            }
        }

        if ($skipReason = $this->shouldSkipGeneration($resultEntityType, $sourceClassName, $className)) {
            return $skipReason;
        }

        $generatorClass = $this->_generatedEntities[$resultEntityType];
        /** @var EntityAbstract $generator */
        $generator = $this->createGeneratorInstance($generatorClass, $sourceClassName, $className);
        if ($generator !== null) {
            $this->tryToLoadSourceClass($className, $generator);
            if (!($file = $generator->generate())) {
                /** @var $logger LoggerInterface */
                $errors = $generator->getErrors();
                $errors[] = 'Class ' . $className . ' generation error: The requested class did not generate properly, '
                    . 'because the \'generated\' directory permission is read-only. '
                    . 'If --- after running the \'bin/magento setup:di:compile\' CLI command when the \'generated\' '
                    . 'directory permission is set to write --- the requested class did not generate properly, then '
                    . 'you must add the generated class object to the signature of the related construct method, only.';
                $message = implode(PHP_EOL, $errors);
                $this->getLogger()->critical($message);
                throw new \RuntimeException($message);
            }
            if (!$this->definedClasses->isClassLoadableFromMemory($className)) {
                $this->_ioObject->includeFile($file);
            }
            return self::GENERATION_SUCCESS;
        }
    }

    /**
     * Retrieve logger
     *
     * @return LoggerInterface
     */
    private function getLogger()
    {
        if (!$this->logger) {
            $this->logger = $this->getObjectManager()->get(LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * Create entity generator
     *
     * @param string $generatorClass
     * @param string $entityName
     * @param string $className
     * @return EntityAbstract
     */
    protected function createGeneratorInstance($generatorClass, $entityName, $className)
    {
        return $this->getObjectManager()->create(
            $generatorClass,
            ['sourceClassName' => $entityName, 'resultClassName' => $className, 'ioObject' => $this->_ioObject]
        );
    }

    /**
     * Set object manager instance.
     *
     * @param ObjectManagerInterface $objectManager
     * @return $this
     */
    public function setObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        return $this;
    }

    /**
     * Get object manager instance.
     *
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        if (!($this->objectManager instanceof ObjectManagerInterface)) {
            throw new \LogicException(
                "Object manager was expected to be set using setObjectManger() "
                . "before getObjectManager() invocation."
            );
        }
        return $this->objectManager;
    }

    /**
     * Try to load/generate source class to check if it is valid or not.
     *
     * @param string $className
     * @param EntityAbstract $generator
     * @return void
     * @throws \RuntimeException
     */
    protected function tryToLoadSourceClass($className, $generator)
    {
        $sourceClassName = $generator->getSourceClassName();
        if (!$this->definedClasses->isClassLoadable($sourceClassName)) {
            if ($this->generateClass($sourceClassName) !== self::GENERATION_SUCCESS) {
                $phrase = new Phrase(
                    'Source class "%1" for "%2" generation does not exist.',
                    [$sourceClassName, $className]
                );
                throw new \RuntimeException($phrase->__toString());
            }
        }
    }

    /**
     * Perform validation surrounding source and result classes and entity type
     *
     * @param string $resultEntityType
     * @param string $sourceClassName
     * @param string $resultClass
     * @return string|bool
     */
    protected function shouldSkipGeneration($resultEntityType, $sourceClassName, $resultClass)
    {
        if (!$resultEntityType || !$sourceClassName) {
            return self::GENERATION_ERROR;
        } elseif ($this->definedClasses->isClassLoadableFromDisk($resultClass)) {
            $generatedFileName = $this->_ioObject->generateResultFileName($resultClass);
            /**
             * Must handle two edge cases: a competing process has generated the class and written it to disc already,
             * or the class exists in committed code, despite matching pattern to be generated.
             */
            if ($this->_ioObject->fileExists($generatedFileName)
                && !$this->definedClasses->isClassLoadableFromMemory($resultClass)
            ) {
                $this->_ioObject->includeFile($generatedFileName);
            }
            return self::GENERATION_SKIP;
        } elseif (!isset($this->_generatedEntities[$resultEntityType])) {
            throw new \InvalidArgumentException('Unknown generation entity.');
        }
        return false;
    }
}
