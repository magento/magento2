<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Translate\Adapter;
use Magento\Framework\Validator;

/**
 * Factory for \Magento\Framework\Validator and \Magento\Framework\Validator\Builder.
 */
class Factory implements ResetAfterRequestInterface
{
    /**
     * cache key
     *
     * @deprecated
     * @see we don't recommend this approach anymore
     */
    public const CACHE_KEY = __CLASS__;

    /**
     * @var ObjectManagerInterface
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    protected readonly ObjectManagerInterface $_objectManager;

    /**
     * Validator config files
     *
     * @var iterable|null
     */
    protected $_configFiles = null;

    /**
     * @var bool
     */
    private $isDefaultTranslatorInitialized = false;

    /**
     * @var Reader
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly Reader $moduleReader;

    /**
     * Initialize dependencies
     *
     * @param ObjectManagerInterface $objectManager
     * @param Reader $moduleReader
     * @param FrontendInterface $cache @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Reader $moduleReader,
        FrontendInterface $cache
    ) {
        $this->_objectManager = $objectManager;
        $this->moduleReader = $moduleReader;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_configFiles = null;
        $this->isDefaultTranslatorInitialized = false;
    }

    /**
     * Init cached list of validation files
     *
     * @return void
     */
    protected function _initializeConfigList()
    {
        if (!$this->_configFiles) {
            $this->_configFiles = $this->moduleReader->getConfigurationFiles('validation.xml');
        }
    }

    /**
     * Create and set default translator to \Magento\Framework\Validator\AbstractValidator.
     *
     * @return void
     * @throws \Zend_Translate_Exception
     */
    protected function _initializeDefaultTranslator()
    {
        if (!$this->isDefaultTranslatorInitialized) {
            /** @var Adapter $translator */
            $translator = $this->_objectManager->create(Adapter::class);
            AbstractValidator::setDefaultTranslator($translator);
            $this->isDefaultTranslatorInitialized = true;
        }
    }

    /**
     * Get validator config object.
     *
     * Will instantiate \Magento\Framework\Validator\Config
     *
     * @return Config
     * @throws \Zend_Translate_Exception
     */
    public function getValidatorConfig()
    {
        $this->_initializeConfigList();
        $this->_initializeDefaultTranslator();
        return $this->_objectManager->create(
            Config::class,
            ['configFiles' => $this->_configFiles]
        );
    }

    /**
     * Create validator builder instance based on entity and group.
     *
     * @param string $entityName
     * @param string $groupName
     * @param array|null $builderConfig
     * @return Builder
     * @throws \Zend_Translate_Exception
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
     * @return Validator
     * @throws \Zend_Translate_Exception
     */
    public function createValidator($entityName, $groupName, array $builderConfig = null)
    {
        $this->_initializeDefaultTranslator();
        return $this->getValidatorConfig()->createValidator($entityName, $groupName, $builderConfig);
    }
}
