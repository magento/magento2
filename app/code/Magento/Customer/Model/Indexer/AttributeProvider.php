<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Indexer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Indexer\FieldsetInterface;
use Magento\Customer\Model\Resource\Attribute\Collection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;

class AttributeProvider implements FieldsetInterface
{
    /**
     * EAV entity
     */
    const ENTITY = Customer::ENTITY;

    /**
     * @var Attribute[]
     */
    protected $attributes;

    /**
     * @param Config $eavConfig
     * @param Collection $collection
     */
    public function __construct(
        Config $eavConfig,
        Collection $collection
    ) {
        $this->eavConfig = $eavConfig;
        $this->collection = $collection;
    }

    /**
     * Add EAV attribute fields to fieldset
     *
     * @param array $data
     * @return array
     */
    public function addDynamicData(array $data)
    {
        $additionalFields = $this->convert($this->getAttributes(), $data);
        $data['fields'] = $this->merge($data['fields'], $additionalFields);
        return $data;
    }

    /**
     * Retrieve all attributes
     *
     * @return Attribute[]
     */
    private function getAttributes()
    {
        if ($this->attributes === null) {
            $this->attributes = [];
            /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
            $attributes = $this->collection->getItems();
            /** @var \Magento\Eav\Model\Entity\AbstractEntity $entity */
            $entity = $this->eavConfig->getEntityType(static::ENTITY)->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }
            $this->attributes = $attributes;
        }

        return $this->attributes;
    }

    /**
     * Convert attributes to fields
     *
     * @param Attribute[] $attributes
     * @param array $fieldset
     * @return array
     */
    protected function convert(array $attributes, array $fieldset)
    {
        $fields = [];
        foreach ($attributes as $attribute) {
            if ($attribute->getBackendType() != 'static') {
                if ($attribute->getData('is_used_in_grid')) {
                    $fields[$attribute->getName()] = [
                        'name' => $attribute->getName(),
                        'handler' => 'Magento\Indexer\Model\Handler\AttributeHandler',
                        'origin' => $attribute->getName(),
                        'type' => $this->getType($attribute),
                        'dataType' => $this->getBackendType($attribute),
                        'filters' => [],
                        'entity' => static::ENTITY,
                        'bind' => isset($fieldset['references']['customer']['to'])
                            ? $fieldset['references']['customer']['to']
                            : null,
                    ];
                }
            } else {
                $fields[$attribute->getName()] = [
                    'type' => $this->getType($attribute),
                    'dataType' => $this->getBackendType($attribute),
                ];
            }
        }

        return $fields;
    }

    /**
     * Get backend type for attribute
     *
     * @param Attribute $attribute
     * @return string
     */
    protected function getBackendType(Attribute $attribute)
    {
        return $attribute->getBackendTypeByInput($attribute->getFrontendInput());
    }

    /**
     * Get field type for attribute
     *
     * @param Attribute $attribute
     * @return string
     */
    protected function getType(Attribute $attribute)
    {
        if (
            in_array($this->getBackendType($attribute), ['varchar', 'text'])
            && $attribute->getData('is_searchable_in_grid')
        ) {
            $type = 'searchable';
        } elseif ($attribute->getData('is_filterable_in_grid')) {
            $type = 'filterable';
        } else {
            $type = 'virtual';
        }

        return $type;
    }

    /**
     * Merge fields with attribute fields
     *
     * @param array $dataFields
     * @param array $searchableFields
     * @return array
     */
    protected function merge(array $dataFields, array $searchableFields)
    {
        foreach ($searchableFields as $name => $field) {
            if (!isset($field['name']) && !isset($dataFields[$name])) {
                continue;
            }
            if (!isset($dataFields[$name])) {
                $dataFields[$name] = [];
            }
            foreach ($field as $key => $value) {
                $dataFields[$name][$key] = $value;
            }
        }

        return $dataFields;
    }
}
