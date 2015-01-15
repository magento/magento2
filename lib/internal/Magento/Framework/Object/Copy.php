<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Utility class for copying data sets between objects
 */
namespace Magento\Framework\Object;

class Copy
{
    /**
     * @var \Magento\Framework\Object\Copy\Config
     */
    protected $_fieldsetConfig;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Object\Copy\Config $fieldsetConfig
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Object\Copy\Config $fieldsetConfig
    ) {
        $this->_eventManager = $eventManager;
        $this->_fieldsetConfig = $fieldsetConfig;
    }

    /**
     * Copy data from object|array to object|array containing fields from fieldset matching an aspect.
     *
     * Contents of $aspect are a field name in target object or array.
     * If targetField attribute is not provided - will be used the same name as in the source object or array.
     *
     * @param string $fieldset
     * @param string $aspect
     * @param array|\Magento\Framework\Object $source
     * @param array|\Magento\Framework\Object $target
     * @param string $root
     * @return array|\Magento\Framework\Object|null the value of $target
     */
    public function copyFieldsetToTarget($fieldset, $aspect, $source, $target, $root = 'global')
    {
        if (!$this->_isFieldsetInputValid($source, $target)) {
            return null;
        }
        $fields = $this->_fieldsetConfig->getFieldset($fieldset, $root);
        if (is_null($fields)) {
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

            if ($targetIsArray) {
                $target[$targetCode] = $value;
            } else {
                $target->setDataUsingMethod($targetCode, $value);
            }
        }

        $eventName = sprintf('core_copy_fieldset_%s_%s', $fieldset, $aspect);
        $this->_eventManager->dispatch($eventName, ['target' => $target, 'source' => $source, 'root' => $root]);

        return $target;
    }

    /**
     * Get data from object|array to object|array containing fields
     * from fieldset matching an aspect.
     *
     * @param string $fieldset
     * @param string $aspect a field name
     * @param array|\Magento\Framework\Object $source
     * @param string $root
     * @return array $data
     */
    public function getDataFromFieldset($fieldset, $aspect, $source, $root = 'global')
    {
        if (!(is_array($source) || $source instanceof \Magento\Framework\Object)) {
            return null;
        }
        $fields = $this->_fieldsetConfig->getFieldset($fieldset, $root);
        if (is_null($fields)) {
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
     * @param array|\Magento\Framework\Object $source
     * @param array|\Magento\Framework\Object $target
     * @return bool
     */
    protected function _isFieldsetInputValid($source, $target)
    {
        return (is_array(
            $source
        ) || $source instanceof \Magento\Framework\Object) && (is_array(
            $target
        ) || $target instanceof \Magento\Framework\Object);
    }

    /**
     * Get value of source by code
     *
     * @param \Magento\Framework\Object|array $source
     * @param string $code
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function _getFieldsetFieldValue($source, $code)
    {
        if (is_array($source)) {
            $value = isset($source[$code]) ? $source[$code] : null;
        } elseif ($source instanceof \Magento\Framework\Object) {
            $value = $source->getDataUsingMethod($code);
        } else {
            throw new \InvalidArgumentException('Source should be array or Magento Object');
        }
        return $value;
    }
}
