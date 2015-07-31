<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Indexer;

use Magento\Customer\Model\Customer;
use Magento\Indexer\Model\FieldsetInterface;
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
     * @param array $data
     * @return array
     */
    public function addDynamicData(array $data)
    {
        $additionalFields = $this->convert($this->getAttributes());
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
     * @param Attribute[] $attributes
     * @return array
     */
    protected function convert(array $attributes)
    {
        $fields = [];
        foreach ($attributes as $attribute) {
            if ($attribute->getBackendType() != 'static') {
                if ($attribute->getData('is_used_in_grid')) {
                    $fields[$attribute->getName()] = [
                        'name' => $attribute->getName(),
                        'handler' => null,
                        'origin' => $attribute->getName(),
                        'type' => $this->getType($attribute),
                        'dataType' => $attribute->getBackendType(),
                        'filters' => [],
                    ];
                }
            } else {
                $fields[$attribute->getName()] = [
                    'type' => $this->getType($attribute),
                ];
            }
        }

        return $fields;
    }

    /**
     * @param Attribute $attribute
     * @return string
     */
    protected function getType(Attribute $attribute)
    {
        if ($attribute->getData('is_searchable_in_grid')) {
            $type = 'searchable';
        } elseif ($attribute->getData('is_filterable_in_grid')) {
            $type = 'filterable';
        } else {
            $type = 'virtual';
        }

        return $type;
    }

    /**
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

    /**
     * @return string
     */
    public function getDefaultHandler()
    {
    }
}
