<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element\Dependency;

class Mapper
{
    /**
     * Field locator model
     *
     * @var \Magento\Config\Model\Config\Structure\SearchInterface
     */
    protected $_fieldLocator;

    /**
     * Dependency Field model
     *
     * @var FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Config\Model\Config\Structure\SearchInterface $fieldLocator
     * @param FieldFactory $fieldFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure\SearchInterface $fieldLocator,
        FieldFactory $fieldFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_fieldLocator = $fieldLocator;
        $this->_fieldFactory = $fieldFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve field dependencies
     *
     * @param array $dependencies
     * @param string $storeCode
     * @param string $fieldPrefix
     * @return array
     */
    public function getDependencies($dependencies, $storeCode, $fieldPrefix = '')
    {
        $output = [];

        foreach ($dependencies as $depend) {
            $field = $this->_fieldFactory->create(['fieldData' => $depend, 'fieldPrefix' => $fieldPrefix]);
            $shouldAddDependency = true;
            /** @var \Magento\Config\Model\Config\Structure\Element\Field $dependentField  */
            $dependentField = $this->_fieldLocator->getElement($depend['id']);
            /*
             * If dependent field can't be shown in current scope and real dependent config value
             * is not equal to preferred one, then hide dependence fields by adding dependence
             * based on not shown field (not rendered field)
             */
            if (false == $dependentField->isVisible()) {
                $valueInStore = $this->_scopeConfig->getValue(
                    $dependentField->getPath($fieldPrefix),
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeCode
                );
                $shouldAddDependency = !$field->isValueSatisfy($valueInStore);
            }
            if ($shouldAddDependency) {
                $output[$field->getId()] = $field;
            }
        }
        return $output;
    }
}
