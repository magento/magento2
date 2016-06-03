<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup\AttributeConfiguration;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\AttributeConfiguration\Provider\ProviderInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class MainConfiguration
{
    /**
     * @var DataObject
     */
    private $attributeConfig;

    /**
     * @var ProviderInterface
     */
    private $frontendInputTypeProvider;

    /**
     * @var ProviderInterface
     */
    private $scopeProvider;

    /**
     * @param ProviderInterface $frontendInputTypeProvider
     * @param ProviderInterface $scopeProvider
     */
    public function __construct(ProviderInterface $frontendInputTypeProvider, ProviderInterface $scopeProvider)
    {
        $this->attributeConfig = new DataObject();
        $this->frontendInputTypeProvider = $frontendInputTypeProvider;
        $this->scopeProvider = $scopeProvider;
    }

    /**
     * @param string $type
     * @return MainConfiguration
     */
    public function withAttributeModel($type)
    {
        return $this->getNewInstanceWithProperty('attribute_model', (string) $type);
    }

    /**
     * @param string $type
     * @return MainConfiguration
     * @see \Magento\Eav\Model\Entity\Attribute\Backend\BackendInterface
     */
    public function withBackendModel($type)
    {
        return $this->getNewInstanceWithProperty('backend', (string) $type);
    }

    /**
     * @param string $backendType
     * @return MainConfiguration
     */
    public function withBackendType($backendType)
    {
        return $this->getNewInstanceWithProperty('type', (string) $backendType);
    }

    /**
     * @param string $backendTable
     * @return MainConfiguration
     */
    public function withBackendTable($backendTable)
    {
        return $this->getNewInstanceWithProperty('table', (string) $backendTable);
    }

    /**
     * @param string $type
     * @return MainConfiguration
     * @see \Magento\Eav\Model\Entity\Attribute\Frontend\FrontendInterface
     */
    public function withFrontendModel($type)
    {
        return $this->getNewInstanceWithProperty('frontend', (string) $type);
    }

    /**
     * @param mixed $inputType
     * @return MainConfiguration
     */
    public function withFrontendInput($inputType)
    {
        $inputType = $this->frontendInputTypeProvider->resolve($inputType);
        return $this->getNewInstanceWithProperty('input', (string) $inputType);
    }

    /**
     * @param string $frontendLabel
     * @return MainConfiguration
     */
    public function withFrontendLabel($frontendLabel)
    {
        return $this->getNewInstanceWithProperty('label', (string) $frontendLabel);
    }

    /**
     * @param string[] $frontendCssClasses
     * @return MainConfiguration
     */
    public function withFrontendCssClasses(array $frontendCssClasses)
    {
        return $this->getNewInstanceWithProperty('frontend_class', implode(' ', $frontendCssClasses));
    }

    /**
     * @param string $type
     * @return MainConfiguration
     * @see \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     */
    public function withSourceModel($type)
    {
        return $this->getNewInstanceWithProperty('source', (string) $type);
    }

    /**
     * @param string $defaultValue
     * @return MainConfiguration
     */
    public function withDefaultValue($defaultValue)
    {
        return $this->getNewInstanceWithProperty('default', (string) $defaultValue, false);
    }

    /**
     * @param string $note
     * @return MainConfiguration
     */
    public function withNote($note)
    {
        return $this->getNewInstanceWithProperty('note', (string) $note);
    }

    /**
     * @param bool $flag
     * @return MainConfiguration
     */
    public function required($flag = true)
    {
        return $this->getNewInstanceWithProperty('required', (bool) $flag, false);
    }

    /**
     * @param bool $flag
     * @return MainConfiguration
     */
    public function unique($flag = true)
    {
        return $this->getNewInstanceWithProperty('unique', (bool) $flag, false);
    }

    /**
     * @param bool $flag
     * @return MainConfiguration
     */
    public function userDefined($flag = true)
    {
        return $this->getNewInstanceWithProperty('user_defined', (bool) $flag, false);
    }

    /**
     * @param int $scope
     * @return MainConfiguration
     * @throws LocalizedException On invalid scope
     */
    public function withScope($scope)
    {
        $scope = $this->scopeProvider->resolve($scope);
        return $this->getNewInstanceWithProperty('global', $scope, false);
    }

    /**
     * @return MainConfiguration
     */
    public function withStoreScope()
    {
        return $this->withScope(ScopedAttributeInterface::SCOPE_STORE);
    }

    /**
     * @return MainConfiguration
     */
    public function withWebsiteScope()
    {
        return $this->withScope(ScopedAttributeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return MainConfiguration
     */
    public function withGlobalScope()
    {
        return $this->withScope(ScopedAttributeInterface::SCOPE_GLOBAL);
    }

    /**
     * @param int $sortOrder
     * @return MainConfiguration
     * @throws LocalizedException On non-integer sort order
     */
    public function withSortOrder($sortOrder)
    {
        if (!is_int($sortOrder)) {
            throw new LocalizedException(__('Non-integer attribute sort order provided.'));
        }
        return $this->getNewInstanceWithProperty('sort_order', $sortOrder, false);
    }

    /**
     * @param string $groupName
     * @return MainConfiguration
     */
    public function withGroup($groupName)
    {
        return $this->getNewInstanceWithProperty('group', (string) $groupName);
    }

    /**
     * @param array $options
     * @return MainConfiguration
     */
    public function withOptions(array $options)
    {
        return $this->getNewInstanceWithProperty('option', $options);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributeConfig->toArray();
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->attributeConfig = new DataObject($this->attributeConfig->toArray());
        $this->frontendInputTypeProvider = clone $this->frontendInputTypeProvider;
        $this->scopeProvider = clone $this->scopeProvider;
    }

    /**
     * @param string $propertyName
     * @param mixed $propertyValue
     * @param bool $withValueCheck
     * @return MainConfiguration
     * @throws LocalizedException
     */
    private function getNewInstanceWithProperty($propertyName, $propertyValue, $withValueCheck = true)
    {
        if ($withValueCheck && empty($propertyValue)) {
            throw new LocalizedException(__('Value of property "%1" is empty', $propertyName));
        }

        $newInstance = clone $this;
        $newInstance->attributeConfig->setData($propertyName, $propertyValue);
        return $newInstance;
    }
}
