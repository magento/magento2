<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\DataProvider;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\UniversalFactory;

/**
 * Class Metadata
 */
class Metadata implements \Iterator, \ArrayAccess
{
    /**
     * Node name of children data sources
     */
    const CHILD_DATA_SOURCES = 'childDataSources';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected $dataSet;

    /**
     * @var array
     */
    protected $children;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var UniversalFactory
     */
    protected $universalFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $manager
     * @param UniversalFactory $universalFactory
     * @param array $config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $manager,
        UniversalFactory $universalFactory,
        array $config
    ) {
        $this->config = $config;
        if (isset($this->config['children'])) {
            $this->config['fields'][self::CHILD_DATA_SOURCES] = $config['children'];
        }
        $this->dataSet = $objectManager->get($this->config['dataSet']);
        $this->manager = $manager;
        $this->universalFactory = $universalFactory;
        $this->initAttributes();

        foreach ($this->config['fields'] as $name => & $field) {
            $this->prepare($name, $field);
        }
    }

    /**
     * Return Data Source fields
     *
     * @return array
     */
    public function getFields()
    {
        return isset($this->config['fields']) ? $this->config['fields'] : [];
    }

    /**
     * Return Data Source children
     *
     * @return array
     */
    public function getChildren()
    {
        return isset($this->config['children']) ? $this->config['children'] : [];
    }

    /**
     * Return Data Source label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->config['label'];
    }

    /**
     * Reset the Collection to the first element
     *
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->config['fields']);
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->config['fields']);
    }

    /**
     * Return the key of the current element
     *
     * @return string
     */
    public function key()
    {
        return key($this->config['fields']);
    }

    /**
     * Move forward to next element
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->config['fields']);
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return (bool)$this->key();
    }

    /**
     * Returns price class by code
     *
     * @param string $code
     * @return string|array
     */
    public function get($code)
    {
        return isset($this->config['fields'][$code]) ? $this->config['fields'][$code] : false;
    }

    /**
     * The value to set.
     *
     * @param string $offset
     * @param string $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->config['fields'][] = $value;
        } else {
            $this->config['fields'][$offset] = $value;
        }
    }

    /**
     * The return value will be casted to boolean if non-boolean was returned.
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->config['fields'][$offset]);
    }

    /**
     * The offset to unset.
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->config['fields'][$offset]);
    }

    /**
     * The offset to retrieve.
     *
     * @param string $offset
     * @return string
     */
    public function offsetGet($offset)
    {
        return isset($this->config['fields'][$offset]) ? $this->config['fields'][$offset] : null;
    }

    /**
     * @return void
     */
    protected function initAttributes()
    {
        if (empty($this->attributes)) {
            foreach ($this->config['fields'] as $field) {
                if (isset($field['source']) && $field['source'] == 'eav') {
                    $attribute = $this->dataSet->getEntity()->getAttribute($field['name']);
                    if ($attribute) {
                        $this->attributes[$field['name']] = $attribute->getData();
                        $options = [];
                        if ($attribute->usesSource()) {
                            $options = $attribute->getSource()->getAllOptions();
                        }
                        $this->attributes[$field['name']]['options'] = $options;
                        $this->attributes[$field['name']]['is_required'] = $attribute->getIsRequired();
                    }
                }
            }
        }
    }

    /**
     * @param string $name
     * @param array $field
     * @return void
     */
    protected function prepare($name, array & $field)
    {
        if ($name == self::CHILD_DATA_SOURCES) {
            foreach ($field as $childName => $childConfig) {
                $field[$childName] = $this->manager->getMetadata($childName);
            }
            return;
        }

        $options = [];
        if (isset($field['source'])) {
            if ($field['source'] == 'option') {
                $rawOptions = $this->manager->getData(
                    $field['reference']['target']
                );
                $options[] = [
                    'label' => __('Please, select...'),
                    'value' => null,
                ];
                foreach ($rawOptions as $rawOption) {
                    $options[] = [
                        'label' => $rawOption[$field['reference']['neededField']],
                        'value' => $rawOption[$field['reference']['targetField']],

                    ];
                }
            }
        } else {
            if (isset($field['optionProvider'])) {
                list($source, $method) = explode('::', $field['optionProvider']);
                $sourceModel = $this->universalFactory->create($source);
                $options = $sourceModel->$method();
            }
        }

        $attributeCodes = [
            'options' => ['eav_map' => 'options', 'default' => $options],
            'dataType' => ['eav_map' => 'frontend_input', 'default' => 'text'],
            'filterType' => ['default' => 'input_filter'],
            'formElement' => ['default' => 'input'],
            'displayArea' => ['default' => 'body'],
            'visible' => ['eav_map' => 'is_visible', 'default' => true],
            'required' => ['eav_map' => 'is_required', 'default' => false],
            'label' => ['eav_map' => 'frontend_label'],
            'sortOrder' => ['eav_map' => 'sort_order'],
            'notice' => ['eav_map' => 'note'],
            'default' => ['eav_map' => 'default_value'],
            'unique' => [],
            'description' => [],
            'constraints' => [],
            'customEntry' => [],
            'size' => ['eav_map' => 'scope_multiline_count'],
            'tooltip' => [],
            'fieldGroup' => [],
        ];

        foreach ($attributeCodes as $code => $info) {
            if (!isset($field[$code])) {
                if (isset($this->attributes[$name]) && isset($info['eav_map'])) {
                    $field[$code] = $this->attributes[$name][$info['eav_map']];
                } elseif (empty($field[$code]) && !empty($info['default'])) {
                    $field[$code] = $info['default'];
                }
            }
        }

        if (isset($field['required']) && $field['required']) {
            $field['constraints']['validate']['required-entry'] = true;
        }
    }
}
