<?php
/**
 * Magento validator config factory
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Validator;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManager
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
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
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
        $translator->setOptions(array('translator' => $translatorCallback));
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
        return $this->_objectManager->create('Magento\Framework\Validator\Config', array('configFiles' => $this->_configFiles));
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
