<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $sourceIsArray = is_array($source);
        $sourceIsDataObject = $source instanceof \Magento\Framework\DataObject;
        $sourceIsExtensible = $source instanceof \Magento\Framework\Api\ExtensibleDataInterface;
        $sourceIsAbstract = $source instanceof \Magento\Framework\Api\AbstractSimpleObject;
        $targetIsArray = is_array($target);
        $targetIsDataObject = $target instanceof \Magento\Framework\DataObject;
        $targetIsExtensible = $target instanceof \Magento\Framework\Api\ExtensibleDataInterface;
        $targetIsAbstract = $target instanceof \Magento\Framework\Api\AbstractSimpleObject;
        if (!(($sourceIsArray || $sourceIsDataObject ||
            $sourceIsExtensible ||
            $sourceIsAbstract) && (
            $targetIsArray || $targetIsDataObject ||
            $targetIsExtensible ||
            $targetIsAbstract))) {
            return null;
        }
        $fields = $this->fieldsetConfig->getFieldset($fieldset, $root);
        if ($fields === null) {
            return $target;
        }
       

        if ($sourceIsArray) {
            if ($targetIsArray) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = isset($source[$code]) ? $source[$code] : null;
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target[$targetCode] = $value;
                }
            } else if ($targetIsDataObject) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = isset($source[$code]) ? $source[$code] : null;
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target->setDataUsingMethod($targetCode, $value);
                }
            } else if ($targetIsExtensible) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = isset($source[$code]) ? $source[$code] : null;
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $this->setAttributeValueFromExtensibleDataObject($target, $targetCode, $value);
                }
            } else if ($targetIsAbstract) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = isset($source[$code]) ? $source[$code] : null;
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target->setData($targetCode, $value);
                }
            } else {
                throw new \InvalidArgumentException(
                'Target should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
                );
            }
        } else if ($sourceIsDataObject) {
            if ($targetIsArray) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = $source->getDataUsingMethod($code);
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target[$targetCode] = $value;
                }
            } else if ($targetIsDataObject) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = $source->getDataUsingMethod($code);
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target->setDataUsingMethod($targetCode, $value);
                }
            } else if ($targetIsExtensible) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = $source->getDataUsingMethod($code);
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $this->setAttributeValueFromExtensibleDataObject($target, $targetCode, $value);
                }
            } else if ($targetIsAbstract) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = $source->getDataUsingMethod($code);
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target->setData($targetCode, $value);
                }
            } else {
                throw new \InvalidArgumentException(
                'Target should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
                );
            }
        } else if ($sourceIsExtensible) {
            if ($targetIsArray) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = $this->getAttributeValueFromExtensibleDataObject($source, $code);
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target[$targetCode] = $value;
                }
            } else if ($targetIsDataObject) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = $this->getAttributeValueFromExtensibleDataObject($source, $code);
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target->setDataUsingMethod($targetCode, $value);
                }
            } else if ($targetIsExtensible) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = $this->getAttributeValueFromExtensibleDataObject($source, $code);
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $this->setAttributeValueFromExtensibleDataObject($target, $targetCode, $value);
                }
            } else if ($targetIsAbstract) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $value = $this->getAttributeValueFromExtensibleDataObject($source, $code);
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target->setData($targetCode, $value);
                }
            } else {
                throw new \InvalidArgumentException(
                'Target should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
                );
            }
        } else if ($sourceIsAbstract) {
            if ($targetIsArray) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $sourceArray = $source->__toArray();
                    $value = isset($sourceArray[$code]) ? $sourceArray[$code] : null;
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target[$targetCode] = $value;
                }
            } else if ($targetIsDataObject) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $sourceArray = $source->__toArray();
                    $value = isset($sourceArray[$code]) ? $sourceArray[$code] : null;
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target->setDataUsingMethod($targetCode, $value);
                }
            } else if ($targetIsExtensible) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $sourceArray = $source->__toArray();
                    $value = isset($sourceArray[$code]) ? $sourceArray[$code] : null;
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $this->setAttributeValueFromExtensibleDataObject($target, $targetCode, $value);
                }
            } else if ($targetIsAbstract) {
                foreach ($fields as $code => $node) {
                    if (empty($node[$aspect])) {
                        continue;
                    }
                    $sourceArray = $source->__toArray();
                    $value = isset($sourceArray[$code]) ? $sourceArray[$code] : null;
                    $targetCode = (string) $node[$aspect];
                    $targetCode = $targetCode == '*' ? $code : $targetCode;

                    $target->setData($targetCode, $value);
                }
            } else {
                throw new \InvalidArgumentException(
                'Target should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
                );
            }
        } else {

            throw new \InvalidArgumentException(
            'Source should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject'
            );
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
        $sourceIsArray = is_array($source);
        $sourceIsDataObject = $source instanceof \Magento\Framework\DataObject;
        $sourceIsExtensible = $source instanceof \Magento\Framework\Api\ExtensibleDataInterface;
        $sourceIsAbstract = $source instanceof \Magento\Framework\Api\AbstractSimpleObject;
        if (!($sourceIsArray || $sourceIsDataObject)) {
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

            if ($sourceIsArray) {
                $value = isset($source[$code]) ? $source[$code] : null;
            } elseif ($sourceIsDataObject) {
                $value = $source->getDataUsingMethod($code);
            }

            $targetCode = (string)$node[$aspect];
            $targetCode = $targetCode == '*' ? $code : $targetCode;
            $data[$targetCode] = $value;
        }

        return $data;
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
