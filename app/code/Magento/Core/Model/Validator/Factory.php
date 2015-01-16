<?php
/**
 * Magento validator config factory
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Validator;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Validator config files
     *
     * @var array|null
     */
    protected $_configFiles = null;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->_objectManager = $objectManager;
        $this->_configFiles = $moduleReader->getConfigurationFiles('validation.xml');
        $this->_initializeDefaultTranslator();
    }

    /**
     * Create and set default translator to \Magento\Framework\Validator\AbstractValidator.
     *
     * @return void
     */
    protected function _initializeDefaultTranslator()
    {
        // Pass translations to \Magento\Framework\TranslateInterface from validators
        $translatorCallback = function () {
            $argc = func_get_args();
            return (string)new \Magento\Framework\Phrase(array_shift($argc), $argc);
        };
        /** @var \Magento\Framework\Translate\Adapter $translator */
        $translator = $this->_objectManager->create('Magento\Framework\Translate\Adapter');
        $translator->setOptions(['translator' => $translatorCallback]);
        \Magento\Framework\Validator\AbstractValidator::setDefaultTranslator($translator);
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
        return $this->_objectManager->create('Magento\Framework\Validator\Config', ['configFiles' => $this->_configFiles]);
    }

    /**
     * Create validator builder instance based on entity and group.
     *
     * @param string $entityName
     * @param string $groupName
     * @param array|null $builderConfig
     * @return \Magento\Framework\Validator\Builder
     */
    public function createValidatorBuilder($entityName, $groupName, array $builderConfig = null)
    {
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
        return $this->getValidatorConfig()->createValidator($entityName, $groupName, $builderConfig);
    }
}
