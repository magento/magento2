<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DataObject;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Copy\Config;
use Magento\Framework\Event\ManagerInterface;

/**
 * Utility class for copying data sets between objects
 *
 * @api
 */
class Copy
{
    /**
     * @var Config
     */
    protected $fieldsetConfig;

    /**
     * @var ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * @param ManagerInterface $eventManager
     * @param Config $fieldsetConfig
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(
        ManagerInterface $eventManager,
        Config $fieldsetConfig,
        ExtensionAttributesFactory $extensionAttributesFactory
    ) {
        $this->eventManager = $eventManager;
        $this->fieldsetConfig = $fieldsetConfig;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * Copy data from object|array to object|array containing fields from fieldset matching an aspect.
     *
     * Contents of $aspect are a field name in target object or array.
     * If targetField attribute is not provided - will be used the same name as in the source object or array.
     *
     * @param string $fieldset
     * @param string $aspect
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $target
     * @param string $root
     *
     * @return array|DataObject|null the value of $target
     * @throws \InvalidArgumentException
     */
    public function copyFieldsetToTarget($fieldset, $aspect, $source, $target, $root = 'global')
    {
        if (!$this->_isFieldsetInputValid($source, $target)) {
            return null;
        }
        $fields = $this->fieldsetConfig->getFieldset($fieldset, $root);
        if ($fields === null) {
            return $target;
        }
        $targetIsArray = is_array($target);

        foreach ($fields as $code => $node) {
            if (empty($node[$aspect])) {
                continue;
            }

            $value = $this->_getFieldsetFieldValue($source, $code);

            $targetCode = (string)$node[$aspect];
            $targetCode = $targetCode == '*' ? $code : $targetCode;

            $target = $this->_setFieldsetFieldValue($target, $targetCode, $value);
        }

        $target = $this->dispatchCopyFieldSetEvent($fieldset, $aspect, $source, $target, $root, $targetIsArray);

        return $target;
    }

    /**
     * Dispatch copy fieldset event
     *
     * @param string $fieldset
     * @param string $aspect
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $target
     * @param string $root
     * @param bool $targetIsArray
     *
     * @return DataObject|mixed
     */
    protected function dispatchCopyFieldSetEvent($fieldset, $aspect, $source, $target, $root, $targetIsArray)
    {
        $eventName = sprintf('core_copy_fieldset_%s_%s', $fieldset, $aspect);
        if ($targetIsArray) {
            $target = new DataObject($target);
        }
        $this->eventManager->dispatch(
            $eventName,
            ['target' => $target, 'source' => $source, 'root' => $root]
        );
        if ($targetIsArray) {
            $target = $target->getData();
        }
        return $target;
    }

    /**
     * Get data from object|array to object|array containing fields from fieldset matching an aspect.
     *
     * @param string $fieldset
     * @param string $aspect a field name
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param string $root
     *
     * @return array
     */
    public function getDataFromFieldset($fieldset, $aspect, $source, $root = 'global')
    {
        if ((!$this->isInputArgumentValid($source))) {
            return null;
        }

        $fields = $this->fieldsetConfig->getFieldset($fieldset, $root);
        if ($fields === null) {
            return null;
        }

        $data = [];
        foreach ($fields as $code => $node) {
            if (empty($node[$aspect])) {
                continue;
            }

            $value = $this->_getFieldsetFieldValue($source, $code);

            $targetCode = (string)$node[$aspect];
            $targetCode = $targetCode == '*' ? $code : $targetCode;
            $data[$targetCode] = $value;
        }

        return $data;
    }

    /**
     * Check if source and target are valid input for converting using fieldset
     *
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $target
     *
     * @return bool
     */
    protected function _isFieldsetInputValid($source, $target)
    {
        return $this->isInputArgumentValid($source) && $this->isInputArgumentValid($target);
    }

    /**
     * Verify that we can access data from input object.
     *
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $object
     *
     * @return bool
     */
    private function isInputArgumentValid($object): bool
    {
        return (is_array($object) || $object instanceof DataObject ||
            $object instanceof ExtensibleDataInterface ||
            $object instanceof AbstractSimpleObject);
    }

    /**
     * Get value of source by code
     *
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param string $code
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function _getFieldsetFieldValue($source, $code)
    {
        switch (true) {
            case is_array($source):
                $value = isset($source[$code]) ? $source[$code] : null;
                break;
            case $source instanceof ExtensibleDataInterface:
                $value = $this->getAttributeValueFromExtensibleObject($source, $code);
                break;
            case $source instanceof DataObject:
                $value = $source->getDataUsingMethod($code);
                break;
            case $source instanceof AbstractSimpleObject:
                $sourceArray = $source->__toArray();
                $value = isset($sourceArray[$code]) ? $sourceArray[$code] : null;
                break;
            default:
                throw new \InvalidArgumentException(
                    'Source should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
                );
        }

        return $value;
    }

    /**
     * Set value of target by code
     *
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $target
     * @param string $targetCode
     * @param mixed $value
     *
     * @return array|DataObject|ExtensibleDataInterface|AbstractSimpleObject
     * @throws \InvalidArgumentException
     */
    protected function _setFieldsetFieldValue($target, $targetCode, $value)
    {
        switch (true) {
            case is_array($target):
                $target[$targetCode] = $value;
                break;
            case $target instanceof ExtensibleDataInterface:
                $this->setAttributeValueFromExtensibleObject($target, $targetCode, $value);
                break;
            case $target instanceof DataObject:
                $target->setDataUsingMethod($targetCode, $value);
                break;
            case $target instanceof AbstractSimpleObject:
                $target->setData($targetCode, $value);
                break;
            default:
                throw new \InvalidArgumentException(
                    'Source should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
                );
        }

        return $target;
    }

    /**
     * Access the extension get method
     *
     * @param ExtensibleDataInterface $source
     * @param string $code
     *
     * @return mixed
     * @throws \InvalidArgumentException
     *
     * @deprecated 102.0.3
     * @see \Magento\Framework\DataObject\Copy::getAttributeValueFromExtensibleObject
     */
    protected function getAttributeValueFromExtensibleDataObject($source, $code)
    {
        return $this->getAttributeValueFromExtensibleObject($source, $code);
    }

    /**
     * Get Attribute Value from Extensible Object Data with fallback to DataObject or AbstractSimpleObject.
     *
     * @param ExtensibleDataInterface $source
     * @param string $code
     *
     * @return mixed|null
     */
    private function getAttributeValueFromExtensibleObject(ExtensibleDataInterface $source, string $code)
    {
        $method = 'get' . str_replace('_', '', ucwords($code, '_'));

        $methodExists = method_exists($source, $method);

        if ($methodExists === true) {
            return $source->{$method}();
        }

        $extensionAttributes = $source->getExtensionAttributes();

        if ($extensionAttributes) {
            $methodExists = method_exists($extensionAttributes, $method);
            if ($methodExists) {
                return $extensionAttributes->{$method}();
            }
        }

        if ($source instanceof DataObject) {
            return $source->getDataUsingMethod($code);
        }

        if ($source instanceof AbstractSimpleObject) {
            $sourceArray = $source->__toArray();
            return isset($sourceArray[$code]) ? $sourceArray[$code] : null;
        }

        throw new \InvalidArgumentException('Attribute in object does not exist.');
    }

    /**
     * Access the extension set method
     *
     * @param ExtensibleDataInterface $target
     * @param string $code
     * @param mixed $value
     *
     * @return void
     * @throws \InvalidArgumentException
     *
     * @deprecated 102.0.3
     * @see \Magento\Framework\DataObject\Copy::setAttributeValueFromExtensibleObject
     */
    protected function setAttributeValueFromExtensibleDataObject(ExtensibleDataInterface $target, $code, $value)
    {
        $this->setAttributeValueFromExtensibleObject($target, $code, $value);
    }

    /**
     * Set Attribute Value for Extensible Object Data with fallback to DataObject or AbstractSimpleObject.
     *
     * @param ExtensibleDataInterface $target
     * @param string $code
     * @param mixed $value
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    private function setAttributeValueFromExtensibleObject(ExtensibleDataInterface $target, string $code, $value): void
    {
        $method = 'set' . str_replace('_', '', ucwords($code, '_'));

        $methodExists = method_exists($target, $method);
        if ($methodExists) {
            $target->{$method}($value);
            return;
        }

        $extensionAttributes = $target->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionAttributesFactory->create(get_class($target));
        }

        if (method_exists($extensionAttributes, $method)) {
            $extensionAttributes->{$method}($value);
            $target->setExtensionAttributes($extensionAttributes);
            return;
        }

        if ($target instanceof DataObject) {
            $target->setDataUsingMethod($code, $value);
            return;
        }

        if ($target instanceof AbstractSimpleObject) {
            $target->setData($code, $value);
            return;
        }

        throw new \InvalidArgumentException('Attribute in object does not exist.');
    }
}
