<?php
/**
 * Magento validator config factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Validator;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Phrase;

class Factory
{
    /** cache key */
    const CACHE_KEY = __CLASS__;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Validator config files
     *
     * @var array|null
     */
    protected $_configFiles = null;

    /**
     * @var bool
     */
    private $isDefaultTranslatorInitialized = false;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $applicationRoot;

    /**
     * @var FileIteratorFactory
     */
    private $fileIteratorFactory;

    /**
     * Initialize dependencies
     *
     * @param ObjectManagerInterface $objectManager
     * @param Reader $moduleReader
     * @param FrontendInterface $cache
     * @param DirectoryList $directoryList
     * @param FileIteratorFactory $fileIteratorFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Reader $moduleReader,
        FrontendInterface $cache,
        DirectoryList $directoryList,
        FileIteratorFactory $fileIteratorFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->moduleReader = $moduleReader;
        $this->cache = $cache;
        $this->applicationRoot = rtrim($directoryList->getRoot(), '/') . '/';
        $this->fileIteratorFactory = $fileIteratorFactory;
    }

    /**
     * Init cached list of validation files
     */
    protected function _initializeConfigList()
    {
        if (!$this->_configFiles) {
            $serializedFilePaths = $this->cache->load(self::CACHE_KEY);
            if (!$serializedFilePaths) {
                $this->_configFiles = $this->moduleReader->getConfigurationFiles('validation.xml');
                $relativeFilePaths = $this->getRelativeFilePathsForConfigFiles();
                $this->cache->save(serialize($relativeFilePaths), self::CACHE_KEY);
            } else {
                $absolutePaths = $this->buildAbsolutePathsForConfigFiles($serializedFilePaths);
                $this->_configFiles = $this->fileIteratorFactory->create($absolutePaths);
            }
        }
    }

    /**
     * @param $serializedFilePaths
     * @return array
     */
    protected function buildAbsolutePathsForConfigFiles($serializedFilePaths)
    {
        $absolutePaths = [];
        /** @var array $serializedFilePaths */
        foreach ($serializedFilePaths as $relativePath) {
            $absolutePaths[] = $relativePath[0] === '/' ? $relativePath : $this->applicationRoot . $relativePath;
        }
        return $absolutePaths;
    }

    /**
     * @return array
     */
    protected function getRelativeFilePathsForConfigFiles()
    {
        $relativeFilePaths = [];
        $applicationRootLength = \strlen($this->applicationRoot);
        foreach ($this->_configFiles as $configFile => $fileContent) {
            $relativeFilePaths[] = strpos($configFile, $this->applicationRoot) === 0 ? substr($configFile, $applicationRootLength) : $configFile;
        }
        return $relativeFilePaths;
    }

    /**
     * Create and set default translator to \Magento\Framework\Validator\AbstractValidator.
     *
     * @return void
     */
    protected function _initializeDefaultTranslator()
    {
        if (!$this->isDefaultTranslatorInitialized) {
            // Pass translations to \Magento\Framework\TranslateInterface from validators
            $translatorCallback = function () {
                $argc = func_get_args();
                return (string)new Phrase(array_shift($argc), $argc);
            };
            /** @var \Magento\Framework\Translate\Adapter $translator */
            $translator = $this->_objectManager->create('Magento\Framework\Translate\Adapter');
            $translator->setOptions(['translator' => $translatorCallback]);
            AbstractValidator::setDefaultTranslator($translator);
            $this->isDefaultTranslatorInitialized = true;
        }
    }

    /**
     * Get validator config object.
     *
     * Will instantiate \Magento\Framework\Validator\Config
     *
     * @return \Magento\Framework\Validator\Config
     */
    public function getValidatorConfig()
    {
        $this->_initializeConfigList();
        $this->_initializeDefaultTranslator();
        return $this->_objectManager->create('Magento\Framework\Validator\Config', ['configFiles' => $this->_configFiles]);
    }

    /**
     * Create validator builder instance based on entity and group.
     *
     * @param string $entityName
     * @param string $groupName
     * @param array|null $builderConfig
     * @return \Magento\Framework\Validator\Builder
     * @throws \InvalidArgumentException
     */
    public function createValidatorBuilder($entityName, $groupName, array $builderConfig = null)
    {
        $this->_initializeDefaultTranslator();
        return $this->getValidatorConfig()->createValidatorBuilder($entityName, $groupName, $builderConfig);
    }

    /**
     * Create validator based on entity and group.
     *
     * @param string $entityName
     * @param string $groupName
     * @param array|null $builderConfig
     * @return \Magento\Framework\Validator
     */
    public function createValidator($entityName, $groupName, array $builderConfig = null)
    {
        $this->_initializeDefaultTranslator();
        return $this->getValidatorConfig()->createValidator($entityName, $groupName, $builderConfig);
    }
}
