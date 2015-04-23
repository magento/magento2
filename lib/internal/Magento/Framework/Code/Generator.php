<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\EntityAbstract;

class Generator
{
    const GENERATION_SUCCESS = 'success';

    const GENERATION_ERROR = 'error';

    const GENERATION_SKIP = 'skip';

    /**
     * @var \Magento\Framework\Code\Generator\Io
     */
    protected $_ioObject;

    /**
     * @var string[] of EntityAbstract classes
     */
    protected $_generatedEntities;

    /**
     * @var DefinedClasses
     */
    protected $definedClasses;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param Generator\Io   $ioObject
     * @param array          $generatedEntities
     * @param DefinedClasses $definedClasses
     */
    public function __construct(
        \Magento\Framework\Code\Generator\Io $ioObject = null,
        array $generatedEntities = [],
        DefinedClasses $definedClasses = null
    ) {
        $this->_ioObject = $ioObject
            ?: new \Magento\Framework\Code\Generator\Io(
                new \Magento\Framework\Filesystem\Driver\File()
            );
        $this->definedClasses = $definedClasses ?: new DefinedClasses();
        $this->_generatedEntities = $generatedEntities;
    }

    /**
     * Get generated entities
     *
     * @return string[]
     */
    public function getGeneratedEntities()
    {
        return $this->_generatedEntities;
    }

    /**
     * Generate Class
     *
     * @param string $className
     * @return string | void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     */
    public function generateClass($className)
    {
        // check if source class a generated entity
        $entity = null;
        $entityName = null;
        foreach ($this->_generatedEntities as $entityType => $generatorClass) {
            $entitySuffix = ucfirst($entityType);
            // if $className string ends on $entitySuffix substring
            if (strrpos($className, $entitySuffix) === strlen($className) - strlen($entitySuffix)) {
                $entity = $entityType;
                $entityName = rtrim(
                    substr($className, 0, -1 * strlen($entitySuffix)),
                    '\\'
                );
                break;
            }
        }
        if (!$entity || !$entityName) {
            return self::GENERATION_ERROR;
        } else if ($this->definedClasses->classLoadable($className)) {
            return self::GENERATION_SKIP;
        } else if (!isset($this->_generatedEntities[$entity])) {
            throw new \InvalidArgumentException('Unknown generation entity.');
        }
        $generatorClass = $this->_generatedEntities[$entity];
        /** @var EntityAbstract $generator */
        $generator = $this->createGeneratorInstance($generatorClass, $entityName, $className);
        if ($generator !== null) {
            $this->tryToLoadSourceClass($className, $generator);
            if (!($file = $generator->generate())) {
                $errors = $generator->getErrors();
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(implode(' ', $errors))
                );
            }
            $this->includeFile($file);
            return self::GENERATION_SUCCESS;
        }
    }

    /**
     * @param string $fileName
     * @return void
     */
    public function includeFile($fileName)
    {
        include $fileName;
    }

    /**
     * Create entity generator
     *
     * @param string $generatorClass
     * @param string $entityName
     * @param string $className
     * @return \Magento\Framework\Code\Generator\EntityAbstract
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @return $this
     */
    public function setObjectManager(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        return $this;
    }

    /**
     * Get object manager instance.
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        if (!($this->objectManager instanceof \Magento\Framework\ObjectManagerInterface)) {
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
     * @param \Magento\Framework\Code\Generator\EntityAbstract $generator
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function tryToLoadSourceClass($className, $generator)
    {
        $sourceClassName = $generator->getSourceClassName();
        if (!$this->definedClasses->classLoadable($sourceClassName)) {
            if ($this->generateClass($sourceClassName) !== self::GENERATION_SUCCESS) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        'Source class "%1" for "%2" generation does not exist.',
                        [$sourceClassName, $className]
                    )
                );
            }
        }
    }
}
