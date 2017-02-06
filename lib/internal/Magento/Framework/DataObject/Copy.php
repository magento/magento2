<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Utility class for copying data sets between objects
 */
namespace Magento\Framework\DataObject;

class Copy
{
    /**
     * @var \Magento\Framework\DataObject\Copy\Config
     */
    protected $fieldsetConfig;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DataObject\Copy\Config $fieldsetConfig
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory
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
     * @param array|\Magento\Framework\DataObject $source
     * @param array|\Magento\Framework\DataObject $target
     * @param string $root
     * @return array|\Magento\Framework\DataObject|null the value of $target
     * @throws \InvalidArgumentException
     *
     * @api
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
     * @param array|\Magento\Framework\DataObject $source
     * @param array|\Magento\Framework\DataObject $target
     * @param string $root
     * @param bool $targetIsArray
     * @return \Magento\Framework\DataObject|mixed
     */
    protected function dispatchCopyFieldSetEvent($fieldset, $aspect, $source, $target, $root, $targetIsArray)
    {
        $eventName = sprintf('core_copy_fieldset_%s_%s', $fieldset, $aspect);
        if ($targetIsArray) {
            $target = new \Magento\Framework\DataObject($target);
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
     * Get data from object|array to object|array containing fields
     * from fieldset matching an aspect.
     *
     * @param string $fieldset
     * @param string $aspect a field name
     * @param array|\Magento\Framework\DataObject $source
     * @param string $root
     * @return array $data
     *
     * @api
     */
    public function getDataFromFieldset($fieldset, $aspect, $source, $root = 'global')
    {
        if (!(is_array($source) || $source instanceof \Magento\Framework\DataObject)) {
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
     * @param array|\Magento\Framework\DataObject $source
     * @param array|\Magento\Framework\DataObject $target
     * @return bool
     */
    protected function _isFieldsetInputValid($source, $target)
    {
        return (is_array($source) || $source instanceof \Magento\Framework\DataObject ||
            $source instanceof \Magento\Framework\Api\ExtensibleDataInterface ||
            $source instanceof \Magento\Framework\Api\AbstractSimpleObject) && (
            is_array($target) || $target instanceof \Magento\Framework\DataObject ||
            $target instanceof \Magento\Framework\Api\ExtensibleDataInterface ||
            $target instanceof \Magento\Framework\Api\AbstractSimpleObject);
    }

    /**
     * Get value of source by code
     *
     * @param mixed $source
     * @param string $code
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function _getFieldsetFieldValue($source, $code)
    {
        if (is_array($source)) {
            $value = isset($source[$code]) ? $source[$code] : null;
        } elseif ($source instanceof \Magento\Framework\DataObject) {
            $value = $source->getDataUsingMethod($code);
        } elseif ($source instanceof \Magento\Framework\Api\ExtensibleDataInterface) {
            $value = $this->getAttributeValueFromExtensibleDataObject($source, $code);
        } elseif ($source instanceof \Magento\Framework\Api\AbstractSimpleObject) {
            $sourceArray = $source->__toArray();
            $value = isset($sourceArray[$code]) ? $sourceArray[$code] : null;
        } else {
            throw new \InvalidArgumentException(
                'Source should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
            );
        }
        return $value;
    }

    /**
     * Set value of target by code
     *
     * @param mixed $target
     * @param string $targetCode
     * @param mixed $value
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function _setFieldsetFieldValue($target, $targetCode, $value)
    {
        $targetIsArray = is_array($target);

        if ($targetIsArray) {
            $target[$targetCode] = $value;
        } else if ($target instanceof \Magento\Framework\DataObject) {
            $target->setDataUsingMethod($targetCode, $value);
        } else if ($target instanceof \Magento\Framework\Api\ExtensibleDataInterface) {
            $this->setAttributeValueFromExtensibleDataObject($target, $targetCode, $value);
        } elseif ($target instanceof \Magento\Framework\Api\AbstractSimpleObject) {
            $target->setData($targetCode, $value);
        } else {
            throw new \InvalidArgumentException(
                'Source should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
            );
        }

        return $target;
    }

    /**
     * Access the extension get method
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface $object
     * @param string $code
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function getAttributeValueFromExtensibleDataObject($source, $code)
    {
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $code)));

        $methodExists = method_exists($source, $method);
        if ($methodExists == true) {
            $value = $source->{$method}();
        } else {
            // If we couldn't find the method, check if we can get it from the extension attributes
            $extensionAttributes = $source->getExtensionAttributes();
            if ($extensionAttributes == null) {
                throw new \InvalidArgumentException('Method in extension does not exist.');
            } else {
                $extensionMethodExists = method_exists($extensionAttributes, $method);
                if ($extensionMethodExists == true) {
                    $value = $extensionAttributes->{$method}();
                } else {
                    throw new \InvalidArgumentException('Attribute in object does not exist.');
                }
            }
        }
        return $value;
    }

    /**
     * Access the extension set method
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface $object
     * @param string $code
     * @param mixed $value
     *
     * @return null
     * @throws \InvalidArgumentException
     */
    protected function setAttributeValueFromExtensibleDataObject($target, $code, $value)
    {
        $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $code)));

        $methodExists = method_exists($target, $method);
        if ($methodExists == true) {
            $target->{$method}($value);
        } else {
            // If we couldn't find the method, check if we can set it from the extension attributes
            $extensionAttributes = $target->getExtensionAttributes();
            if ($extensionAttributes == null) {
                $extensionAttributes = $this->extensionAttributesFactory->create(get_class($target));
            }
            $extensionMethodExists = method_exists($extensionAttributes, $method);
            if ($extensionMethodExists == true) {
                $extensionAttributes->{$method}($value);
                $target->setExtensionAttributes($extensionAttributes);
            } else {
                throw new \InvalidArgumentException('Attribute in object does not exist.');
            }
        }

        return null;
    }
}
